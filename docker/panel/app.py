import os
import subprocess
import requests
import shlex
import re
import shutil
import yaml
import json
from datetime import datetime
from flask import Flask, render_template, request, redirect, url_for, flash, jsonify, Response, session
from flask import stream_with_context
import logging
import time
from functools import wraps
from collections import deque

logging.basicConfig(level=logging.INFO, format='[%(asctime)s] %(levelname)s in %(module)s: %(message)s')
logger = logging.getLogger(__name__)

# Helper untuk memformat timestamp Docker menjadi "YYYY-MM-DD H:i:s" tanpa zona waktu
def format_created_field(s: str) -> str:
    try:
        if not s:
            return ''
        m = re.search(r'(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})', s)
        return m.group(1) if m else s
    except Exception:
        return s

app = Flask(__name__)
app.secret_key = 'nginx-manager-secret-key'
# Default admin credentials (can be overridden via env PANEL_ADMIN_USER/PANEL_ADMIN_PASS)
app.config['ADMIN_USER'] = os.environ.get('PANEL_ADMIN_USER', 'admin')
app.config['ADMIN_PASS'] = os.environ.get('PANEL_ADMIN_PASS', 'admin123')

@app.context_processor
def inject_config():
    return dict(config=app.config)

# Configuration
NGINX_CONF_DIR = os.environ.get('NGINX_CONF_DIR', os.path.join(os.environ.get('HOST_PROJECT_DIR', '/Users/basoro/Slemp/data/www/mlite.loc'), 'docker', 'nginx', 'conf.d'))
NGINX_CONTAINER_NAME = os.environ.get('NGINX_CONTAINER_NAME', 'mlite_nginx')
PHP_CONTAINER_NAME = os.environ.get('PHP_CONTAINER_NAME', 'mlite_php')
# Base web root inside containers (mounted from host ../)
PHP_WEBROOT_BASE = '/var/www/public'
# Optional toggle to create default index.html after directory creation (env CREATE_DEFAULT_INDEX=1/0)
CREATE_DEFAULT_INDEX = os.environ.get('CREATE_DEFAULT_INDEX', '1') == '1'

# Simple in-memory cache for Nginx network stats
NETWORK_STATS_CACHE = {
    'history': deque(maxlen=60),  # keep last 60 samples
    'last_fetch_at': 0,
    'last_value': None
}
NETWORK_STATS_POLL_INTERVAL_SEC = 3

def parse_size_to_bytes(s: str) -> int:
    try:
        s = s.strip()
        parts = s.split()
        if len(parts) == 1:
            val_unit = parts[0]
            num_str = ''.join(ch for ch in val_unit if (ch.isdigit() or ch == '.' or ch == ','))
            unit = ''.join(ch for ch in val_unit if ch.isalpha())
        else:
            num_str, unit = parts[0], parts[1]
        num = float(num_str.replace(',', '')) if num_str else 0.0
        unit = unit.upper() if unit else 'B'
        if unit in ['B']:
            mult = 1
        elif unit in ['KB', 'KIB', 'K']:
            mult = 1024
        elif unit in ['MB', 'MIB', 'M']:
            mult = 1024**2
        elif unit in ['GB', 'GIB', 'G']:
            mult = 1024**3
        elif unit in ['TB', 'TIB', 'T']:
            mult = 1024**4
        else:
            mult = 1
        return int(num * mult)
    except Exception:
        return 0

def get_nginx_netio_once():
    """Sample rx/tx bytes for the nginx container using `docker stats --no-stream`.
    Returns (rx_bytes, tx_bytes)."""
    try:
        result = subprocess.run(
            ['docker', 'stats', '--no-stream', '--format', '{{json .}}', NGINX_CONTAINER_NAME],
            capture_output=True, text=True, timeout=8
        )
        if result.returncode != 0:
            raise RuntimeError(result.stderr or 'docker stats failed')
        line = (result.stdout or '').strip().splitlines()
        if not line:
            raise RuntimeError('no stats output')
        obj = json.loads(line[0])
        netio = obj.get('NetIO', '')
        parts = [p.strip() for p in netio.split('/')]
        rx_str = parts[0] if len(parts) > 0 else '0 B'
        tx_str = parts[1] if len(parts) > 1 else '0 B'
        rx = parse_size_to_bytes(rx_str)
        tx = parse_size_to_bytes(tx_str)
        return rx, tx
    except Exception as e:
        logger.error(f"Error sampling nginx net stats: {e}")
        return 0, 0

def get_container_network_stats():
    """Return time series of nginx container network stats (rx/tx total bytes).
    Uses simple caching to avoid frequent docker calls."""
    now = time.time()
    try:
        if now - (NETWORK_STATS_CACHE['last_fetch_at'] or 0) >= NETWORK_STATS_POLL_INTERVAL_SEC:
            rx, tx = get_nginx_netio_once()
            NETWORK_STATS_CACHE['last_fetch_at'] = now
            NETWORK_STATS_CACHE['last_value'] = (rx, tx)
            NETWORK_STATS_CACHE['history'].append({
                'timestamp': datetime.now().strftime('%H:%M'),
                'rx': rx,
                'tx': tx
            })
        if not NETWORK_STATS_CACHE['history']:
            rx, tx = get_nginx_netio_once()
            NETWORK_STATS_CACHE['last_fetch_at'] = now
            NETWORK_STATS_CACHE['last_value'] = (rx, tx)
            NETWORK_STATS_CACHE['history'].append({
                'timestamp': datetime.now().strftime('%H:%M'),
                'rx': rx,
                'tx': tx
            })
        return list(NETWORK_STATS_CACHE['history'])
    except Exception as e:
        logger.error(f"Error building network stats: {e}")
        return []

def get_sites():
    """Get list of configured sites with detected type and relevant info"""
    sites = []
    try:
        if os.path.exists(NGINX_CONF_DIR):
            for filename in os.listdir(NGINX_CONF_DIR):
                if filename.endswith('.conf'):
                    domain = filename.replace('.conf', '')
                    filepath = os.path.join(NGINX_CONF_DIR, filename)
                    
                    site_type = None
                    port = None
                    root_dir = None
                    try:
                        with open(filepath, 'r') as f:
                            content = f.read()
                            # Detect type and extract information
                            # PHP-FPM detection: parse fastcgi_pass host:9000 to infer version
                            m_fcgi = re.search(r"fastcgi_pass\s+([a-zA-Z0-9_-]+):9000", content)
                            if m_fcgi:
                                site_type = 'php'
                                php_host = m_fcgi.group(1)
                                php_version = None
                                # Map host to version (supports php56–php83)
                                m_ver = re.search(r"php(\d{2})", php_host)
                                if m_ver:
                                    code = m_ver.group(1)
                                    if code == '56':
                                        php_version = '5.6'
                                    elif code == '70':
                                        php_version = '7.0'
                                    elif code == '71':
                                        php_version = '7.1'
                                    elif code == '72':
                                        php_version = '7.2'
                                    elif code == '73':
                                        php_version = '7.3'
                                    elif code == '74':
                                        php_version = '7.4'
                                    elif code == '80':
                                        php_version = '8.0'
                                    elif code == '81':
                                        php_version = '8.1'
                                    elif code == '82':
                                        php_version = '8.2'
                                    elif code == '83':
                                        php_version = '8.3'
                                # extract root directive
                                m = re.search(r"^\s*root\s+([^;]+);", content, re.MULTILINE)
                                if m:
                                    root_dir = m.group(1).strip()
                            elif 'proxy_pass http://localhost:' in content:
                                site_type = 'proxy'
                                for line in content.split('\n'):
                                    if 'proxy_pass http://localhost:' in line:
                                        port = line.split('proxy_pass http://localhost:')[1].split(';')[0].strip()
                                        break
                            else:
                                # Detect static site by try_files fallback to index.html and absence of PHP fastcgi
                                if re.search(r"try_files\s+\$uri\s+\$uri/\s+/index\.html", content) and 'fastcgi_pass' not in content:
                                    site_type = 'static'
                                    m = re.search(r"^\s*root\s+([^;]+);", content, re.MULTILINE)
                                    if m:
                                        root_dir = m.group(1).strip()
                    except Exception as e:
                        print(f"Error reading config file {filename}: {e}")
                    
                    sites.append({
                        'domain': domain,
                        'type': site_type or 'proxy',
                        'port': port,
                        'root': root_dir,
                        'php_version': php_version if site_type == 'php' else None,
                        'config_file': filename,
                        'created_at': datetime.fromtimestamp(os.path.getctime(filepath)).strftime('%Y-%m-%d %H:%M:%S')
                    })
    except Exception as e:
        print(f"Error getting sites: {e}")
    
    return sites

def safe_join(base, path):
    """Safely join and normalize a path ensuring it stays within base."""
    if not path:
        return base
    if not path.startswith('/'):
        path = '/' + path
    # Normalize path
    normalized = os.path.normpath(path)
    # Map any /var/www/public* to container base
    if normalized.startswith('/var/www/public'):
        target = normalized
    else:
        # Constrain to base directory
        target = os.path.normpath(os.path.join(PHP_WEBROOT_BASE, normalized.lstrip('/')))
    # Ensure target stays within base
    base_norm = os.path.normpath(PHP_WEBROOT_BASE)
    if not target.startswith(base_norm):
        raise ValueError('Invalid root directory: must be under /var/www/public')
    return target


def ensure_directory(path):
    """Create directory via Nginx container (shared mount) if not exists with 0755 permissions."""
    try:
        quoted = shlex.quote(path)
        cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"mkdir -p {quoted} && chmod 755 {quoted}"]
        result = subprocess.run(cmd, capture_output=True, text=True)
        if result.returncode == 0:
            return True, 'Directory ensured via container'
        return False, f'Failed to create directory via container: {result.stderr.strip() or result.stdout.strip()}'
    except Exception as e:
        return False, f'Failed to create directory: {str(e)}'

# Create a default index.html for PHP-FPM site root
# The file is written inside the container (nginx/php) since panel container doesn't mount webroot
# Includes site name and current timestamp with simple styling

def create_default_index(root_dir, domain):
    try:
        now = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        index_path = os.path.join(root_dir, 'index.html')
        title = f"Welcome to {domain}"
        content = f"""<!DOCTYPE html>
<html lang=\"en\">
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width,initial-scale=1\">
  <title>{title}</title>
  <style>
    body {{ font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background:#f9fafb; color:#111827; margin:0; }}
    .wrap {{ max-width:800px; margin:10vh auto; background:#fff; padding:24px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,.08); }}
    h1 {{ margin:0 0 12px; font-size:28px; }}
    p {{ margin:6px 0; color:#374151; }}
    code {{ background:#eef2ff; padding:2px 6px; border-radius:6px; }}
  </style>
</head>
<body>
  <div class=\"wrap\">
    <h1>{title}</h1>
    <p>Generated on {now}</p>
    <p>Root directory: <code>{root_dir}</code></p>
    <p>Replace this page with your app or <code>index.php</code>.</p>
  </div>
</body>
</html>
"""
        quoted_index = shlex.quote(index_path)
        last_err = None
        # Write via Nginx container (shared mount). Fallbacks are unnecessary with unified mount.
        try:
            cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"cat > {quoted_index} << 'EOF'\n{content}\nEOF\nchmod 644 {quoted_index}"]
            res = subprocess.run(cmd, capture_output=True, text=True)
            if res.returncode == 0:
                return True, 'Default index.html created'
            last_err = res.stderr.strip() or res.stdout.strip()
        except Exception as e:
            last_err = str(e)
        return False, f'Failed to create index.html: {last_err or "unknown error"}'
    except Exception as e:
        return False, f'Failed to prepare index content: {str(e)}'


def create_nginx_config(domain, site_type='proxy', port=None, root_dir='/var/www/public', php_version='8.3'):
    """Create nginx configuration file for either reverse proxy or PHP-FPM"""
    if site_type == 'php':
        # Map php_version to container host name
        version_map = {
            '5.6': 'php56',
            '7.0': 'php70',
            '7.1': 'php71',
            '7.2': 'php72',
            '7.3': 'php73',
            '7.4': 'php74',
            '8.0': 'php80',
            '8.1': 'php81',
            '8.2': 'php82',
            '8.3': 'php83',
        }
        php_host = version_map.get(php_version, 'php83')
        config_content = f"""server {{
    listen 80;
    server_name {domain};

    root {root_dir};
    index index.php index.html;

    access_log /var/log/nginx/access.log;
    error_log  /var/log/nginx/error.log;

    location / {{
        try_files $uri $uri/ /index.php?$args;
    }}

    location ~ \\.php$ {{
        include fastcgi_params;
        fastcgi_pass {php_host}:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;

        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }}

    location ~ /\\. {{
        deny all;
    }}
}}
"""
    elif site_type == 'static':
        config_content = f"""server {{
    listen 80;
    server_name {domain};

    root {root_dir};
    index index.html;

    access_log /var/log/nginx/access.log;
    error_log  /var/log/nginx/error.log;

    location / {{
        try_files $uri $uri/ /index.html;
    }}

    location ~ /\\. {{
        deny all;
    }}
}}
"""
    else:
        config_content = f"""server {{
    listen 80;
    server_name {domain};
    
    location / {{
        proxy_pass http://localhost:{port};
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }}
}}
"""
    
    config_filename = f"{domain}.conf"
    config_path = os.path.join(NGINX_CONF_DIR, config_filename)
    
    try:
        with open(config_path, 'w') as f:
            f.write(config_content)
        return True
    except Exception as e:
        print(f"Error creating config file: {e}")
        return False

def delete_nginx_config(domain):
    """Delete nginx configuration file"""
    config_filename = f"{domain}.conf"
    config_path = os.path.join(NGINX_CONF_DIR, config_filename)
    
    try:
        if os.path.exists(config_path):
            os.remove(config_path)
            return True
        return False
    except Exception as e:
        print(f"Error deleting config file: {e}")
        return False

def reload_nginx():
    """Reload nginx configuration"""
    try:
        # Test nginx configuration first
        test_result = subprocess.run([
            'docker', 'exec', NGINX_CONTAINER_NAME, 'nginx', '-t'
        ], capture_output=True, text=True)
        
        if test_result.returncode != 0:
            return False, f"Configuration test failed: {test_result.stderr}"
        
        # If test passes, reload nginx
        result = subprocess.run([
            'docker', 'exec', NGINX_CONTAINER_NAME, 'nginx', '-s', 'reload'
        ], capture_output=True, text=True)
        
        if result.returncode == 0:
            return True, "Nginx reloaded successfully"
        else:
            return False, f"Nginx reload failed: {result.stderr}"
    except Exception as e:
        return False, f"Error reloading nginx: {str(e)}"


def ensure_compose_env():
    """Write /workspace/docker/.env with HOST_PROJECT_DIR to ensure Compose variable substitution."""
    try:
        proj_dir = get_project_dir()
        if not proj_dir:
            return False
        env_path = '/workspace/docker/.env'
        with open(env_path, 'w') as f:
            f.write(f"HOST_PROJECT_DIR={proj_dir}\n")
        return True
    except Exception:
        return False

def get_project_dir():
    """Return the host project directory backing /workspace mount of mlite_panel."""
    try:
        res = subprocess.run(
            ['docker','inspect','-f','{{range .Mounts}}{{if eq .Destination "/workspace"}}{{.Source}}{{end}}{{end}}','mlite_panel'],
            capture_output=True, text=True, timeout=5
        )
        if res.returncode == 0:
            src = (res.stdout or '').strip()
            if src:
                return src
    except Exception:
        pass
    # Fallback to env if provided
    return os.environ.get('HOST_PROJECT_DIR', '')

def get_services_from_compose():
    """Get all services from docker-compose.yml"""
    print("[DEBUG] === get_services_from_compose() STARTED ===")
    
    try:
        # Resolve candidate paths (prefer container-mounted first)
        current_file = os.path.abspath(__file__)
        current_dir = os.path.dirname(current_file)
        parent_dir = os.path.dirname(current_dir)
        candidates = [
            ('/app/docker-compose.yml', '/app'),
            (os.path.join(parent_dir, 'docker-compose.yml'), parent_dir),
            ('/workspace/docker/docker-compose.yml', '/workspace/docker'),
            ('/Users/basoro/Slemp/data/www/mlite.loc/docker/docker-compose.yml', '/Users/basoro/Slemp/data/www/mlite.loc/docker')
        ]
        print(f"[DEBUG] Current file: {current_file}")
        print(f"[DEBUG] Current dir: {current_dir}")
        print(f"[DEBUG] Parent dir: {parent_dir}")
        for path, _ in candidates:
            print(f"[DEBUG] Candidate compose path exists? {path}: {os.path.exists(path)}")
        
        compose_path = None
        for path, _ in candidates:
            if os.path.exists(path):
                compose_path = path
                break
        if not compose_path:
            print("[ERROR] No docker-compose.yml found in any checked path")
            print("[DEBUG] === get_services_from_compose() COMPLETED (no file) ===")
            return []
        
        print(f"[DEBUG] Reading compose file: {compose_path}")
        with open(compose_path, 'r') as f:
            content = f.read()
            print(f"[DEBUG] File content length: {len(content)} characters")
            print(f"[DEBUG] First 200 chars:\n{content[:200]}")
            compose_data = yaml.safe_load(content)
        
        print(f"[DEBUG] Parsed compose data type: {type(compose_data)}")
        if not compose_data or not isinstance(compose_data, dict):
            print(f"[ERROR] Compose data invalid or empty: {compose_data}")
            print("[DEBUG] === get_services_from_compose() COMPLETED (invalid data) ===")
            return []
        print(f"[DEBUG] Parsed compose data keys: {list(compose_data.keys())}")
        
        services_data = compose_data.get('services', {})
        print(f"[DEBUG] Found {len(services_data)} services: {list(services_data.keys())}")
        
        services = []
        for service_name, service_config in services_data.items():
            print(f"[DEBUG] Processing service: {service_name}")
            service_info = {
                'name': service_name,
                'container_name': service_config.get('container_name', f"mlite_{service_name}"),
                'image': service_config.get('image', 'custom-build'),
                'build': 'build' in service_config,
                'ports': service_config.get('ports', []),
                'volumes': len(service_config.get('volumes', [])),
                'environment': len(service_config.get('environment', {})),
                'depends_on': service_config.get('depends_on', []),
                'restart': service_config.get('restart', 'no')
            }
            services.append(service_info)
            print(f"[DEBUG] Added service info: {service_info}")
        
        print(f"[DEBUG] Returning {len(services)} services")
        print("[DEBUG] === get_services_from_compose() COMPLETED ===")
        return services
        
    except Exception as e:
        print(f"[ERROR] Exception in get_services_from_compose(): {str(e)}")
        print(f"[ERROR] Exception type: {type(e)}")
        import traceback
        print(f"[ERROR] Traceback: {traceback.format_exc()}")
        print("[DEBUG] === get_services_from_compose() COMPLETED (exception) ===")
        return []

def get_php_containers():
    """Get PHP container data for PHP-FPM page, consistent with docker ps logic."""
    print("[DEBUG] === get_php_containers() STARTED ===")
    try:
        # Use docker ps JSON per-line for reliable status/ports
        cmd = ['docker', 'ps', '-a', '--format', '{{json .}}']
        print(f"[DEBUG] Running: {' '.join(cmd)}")
        result = subprocess.run(cmd, capture_output=True, text=True, check=True)
        
        containers = []
        lines = result.stdout.strip().split('\n') if result.stdout.strip() else []
        print(f"[DEBUG] Found {len(lines)} containers total")
        
        php_versions = ['php56', 'php70', 'php71', 'php72', 'php73', 'php74', 'php80', 'php81', 'php82', 'php83']
        version_map = {
            '56': '5.6', '70': '7.0', '71': '7.1', '72': '7.2', '73': '7.3',
            '74': '7.4', '80': '8.0', '81': '8.1', '82': '8.2', '83': '8.3'
        }
        
        for line in lines:
            if not line.strip():
                continue
            try:
                obj = json.loads(line)
                container_name = obj.get('Names') or obj.get('Name') or ''
                matched_ver = None
                for php_ver in php_versions:
                    if php_ver in container_name:
                        matched_ver = php_ver
                        break
                if not matched_ver:
                    continue
                
                print(f"[DEBUG] Found PHP container: {container_name}")
                status_text = obj.get('Status', '') or ''
                running = status_text.lower().startswith('up')
                ports_parsed = parse_ports_string(obj.get('Ports', '') or '')
                # Convert to display strings like "host:container" or just "container"
                ports_display = []
                for p in deduplicate_ports(ports_parsed):
                    pub = str(p.get('PublishedPort') or '')
                    tgt = str(p.get('TargetPort') or '')
                    if pub and tgt:
                        ports_display.append(f"{pub}:{tgt}")
                    elif tgt:
                        ports_display.append(tgt)
                
                # Derive PHP version code (e.g., 83 -> 8.3)
                code = matched_ver.replace('php', '')
                php_version = version_map.get(code, code)
                
                containers.append({
                    'name': container_name,
                    'running': running,
                    'status': 'Running' if running else 'Stopped',
                    'php_version': php_version,
                    'ports': ports_display,
                    'created': format_created_field(obj.get('CreatedAt', '')),
                    'image': obj.get('Image', '')
                })
            except (json.JSONDecodeError, KeyError, IndexError) as e:
                print(f"[WARN] Error parsing container data: {e}")
                continue
        
        print(f"[DEBUG] Found {len(containers)} PHP containers")
        print("[DEBUG] === get_php_containers() COMPLETED ===")
        return containers
        
    except subprocess.CalledProcessError as e:
        print(f"[ERROR] Error getting PHP containers: {e}")
        print("[DEBUG] === get_php_containers() COMPLETED (error) ===")
        return []
    except Exception as e:
        print(f"[ERROR] Unexpected error getting PHP containers: {e}")
        print("[DEBUG] === get_php_containers() COMPLETED (exception) ===")
        return []

def get_mysql_containers():
    """Get MySQL container data for MySQL management page, consistent with docker ps logic."""
    print("[DEBUG] === get_mysql_containers() STARTED ===")
    try:
        # Use docker ps JSON per-line for reliable status/ports
        cmd = ['docker', 'ps', '-a', '--format', '{{json .}}']
        print(f"[DEBUG] Running: {' '.join(cmd)}")
        result = subprocess.run(cmd, capture_output=True, text=True, check=True)
        
        containers = []
        lines = result.stdout.strip().split('\n') if result.stdout.strip() else []
        print(f"[DEBUG] Found {len(lines)} containers total")
        
        # MySQL container identifiers
        mysql_identifiers = ['mysql', 'mariadb', 'percona']
        
        for line in lines:
            if not line.strip():
                continue
            try:
                obj = json.loads(line)
                container_name = obj.get('Names') or obj.get('Name') or ''
                
                # Check if this is a MySQL container
                is_mysql = False
                for identifier in mysql_identifiers:
                    if identifier in container_name.lower():
                        is_mysql = True
                        break
                
                if not is_mysql:
                    continue
                
                print(f"[DEBUG] Found MySQL container: {container_name}")
                status_text = obj.get('Status', '') or ''
                running = status_text.lower().startswith('up')
                ports_parsed = parse_ports_string(obj.get('Ports', '') or '')
                
                # Convert to display strings like "host:container" or just "container"
                ports_display = []
                for p in deduplicate_ports(ports_parsed):
                    pub = str(p.get('PublishedPort') or '')
                    tgt = str(p.get('TargetPort') or '')
                    if pub and tgt:
                        ports_display.append(f"{pub}:{tgt}")
                    elif tgt:
                        ports_display.append(tgt)
                
                containers.append({
                    'name': container_name,
                    'running': running,
                    'status': 'Running' if running else 'Stopped',
                    'ports': ports_display,
                    'created': obj.get('CreatedAt', ''),
                    'image': obj.get('Image', '')
                })
            except (json.JSONDecodeError, KeyError, IndexError) as e:
                print(f"[WARN] Error parsing container data: {e}")
                continue
        
        print(f"[DEBUG] Found {len(containers)} MySQL containers")
        print("[DEBUG] === get_mysql_containers() COMPLETED ===")
        return containers
        
    except subprocess.CalledProcessError as e:
        print(f"[ERROR] Error getting MySQL containers: {e}")
        print("[DEBUG] === get_mysql_containers() COMPLETED (error) ===")
        return []
    except Exception as e:
        print(f"[ERROR] Unexpected error getting MySQL containers: {e}")
        print("[DEBUG] === get_mysql_containers() COMPLETED (exception) ===")
        return []

def parse_ports_string(ports_str: str):
    """Convert docker ps Ports string into list of dicts with PublishedPort/TargetPort."""
    if not ports_str:
        return []
    parts = [p.strip() for p in ports_str.split(',') if p.strip()]
    results = []
    for p in parts:
        try:
            if '->' in p:
                left, right = p.split('->', 1)
                # PublishedPort: take last number on the left side
                m_pub = re.findall(r"(\d+)", left)
                published = m_pub[-1] if m_pub else ''
                # TargetPort: take number before / on the right side
                target = right.split('/')[0]
                # If right contains host:port mapping like 80,80/tcp ensure only port digits
                m_target = re.findall(r"(\d+)", target)
                target = m_target[-1] if m_target else ''
                results.append({'PublishedPort': published, 'TargetPort': target})
            else:
                # Format like "80/tcp" — only target port exposed
                target = p.split('/')[0]
                m_target = re.findall(r"(\d+)", target)
                target = m_target[-1] if m_target else ''
                results.append({'PublishedPort': '', 'TargetPort': target})
        except Exception:
            # Fallback: skip malformed segment
            continue
    return results

def deduplicate_ports(ports: list):
    """Remove duplicate port mappings based on (PublishedPort, TargetPort).
    Ensures consistent dict shape {PublishedPort, TargetPort}.
    """
    seen = set()
    deduped = []
    for p in ports or []:
        try:
            if isinstance(p, dict):
                pub = str(p.get('PublishedPort', '') or '')
                tgt = str(p.get('TargetPort', '') or '')
            else:
                # Unknown shape, skip
                continue
            key = (pub, tgt)
            if key not in seen:
                seen.add(key)
                deduped.append({'PublishedPort': pub, 'TargetPort': tgt})
        except Exception:
            continue
    return deduped

def get_container_status_via_docker_ps():
    """Get status of containers using `docker ps` (no compose plugin required)."""
    status_by_name = {}
    try:
        cmd = ['docker', 'ps', '-a', '--format', '{{json .}}']
        print(f"[DEBUG] Running: {' '.join(cmd)}")
        result = subprocess.run(cmd, capture_output=True, text=True)
        print(f"[DEBUG] docker ps exit: {result.returncode}")
        if result.stderr:
            print(f"[DEBUG] stderr: {result.stderr.strip()}")
        if result.returncode == 0 and result.stdout.strip():
            lines = result.stdout.strip().split('\n')
            print(f"[DEBUG] docker ps returned {len(lines)} JSON lines")
            for line in lines:
                if not line.strip():
                    continue
                try:
                    obj = json.loads(line)
                except json.JSONDecodeError as e:
                    print(f"[WARN] JSON decode error in docker ps line: {e}")
                    continue
                name = obj.get('Names') or obj.get('Name') or ''
                status_text = obj.get('Status', '')
                running = status_text.lower().startswith('up')
                ports_list = parse_ports_string(obj.get('Ports', '') or '')
                status_by_name[name] = {
                    'running': running,
                    'status': status_text or ('running' if running else 'stopped'),
                    'created': format_created_field(obj.get('CreatedAt', '')),
                    'image': obj.get('Image', ''),
                    'ports': ports_list
                }
        else:
            print("[ERROR] docker ps failed or output empty")
    except Exception as e:
        print(f"Error running docker ps: {e}")
    return status_by_name

def get_container_status():
    """Get status of all containers from docker-compose"""
    container_data = {}

    try:
        # Prefer container-mounted compose file, fallback to parent and host paths
        current_file = os.path.abspath(__file__)
        current_dir = os.path.dirname(current_file)
        parent_dir = os.path.dirname(current_dir)
        candidates = [
            ('/app/docker-compose.yml', '/app'),
            (os.path.join(parent_dir, 'docker-compose.yml'), parent_dir),
            ('/workspace/docker/docker-compose.yml', '/workspace/docker'),
            ('/Users/basoro/Slemp/data/www/mlite.loc/docker/docker-compose.yml', '/Users/basoro/Slemp/data/www/mlite.loc/docker')
        ]

        compose_path = None
        compose_cwd = None
        for path, cwd in candidates:
            if os.path.exists(path):
                compose_path = path
                compose_cwd = cwd
                break

        if not compose_path:
            print("[ERROR] No docker-compose.yml found for container status")
            return container_data

        base = get_compose_cmd_base()
        project = get_compose_project()
        cmd = base + ['-f', compose_path] + (['--project-name', project] if project else []) + ['ps', '--format', 'json']
        print(f"[DEBUG] Running: {' '.join(cmd)} (cwd={compose_cwd})")
        result = subprocess.run(cmd, capture_output=True, text=True, cwd=compose_cwd)
        print(f"[DEBUG] docker compose ps exit: {result.returncode}")
        if result.stderr:
            print(f"[DEBUG] stderr: {result.stderr.strip()}")

        if result.returncode == 0 and result.stdout.strip():
            try:
                lines = result.stdout.strip().split('\n')
                print(f"[DEBUG] ps returned {len(lines)} JSON lines")
                containers = []
                for line in lines:
                    if line.strip():
                        try:
                            containers.append(json.loads(line))
                        except json.JSONDecodeError as e:
                            print(f"[WARN] JSON decode error: {e}")
                for container in containers:
                    service_name = container.get('Service', '')
                    container_data[service_name] = {
                        'running': 'running' in (container.get('State', '') or '').lower(),
                        'status': container.get('State', 'unknown'),
                        'created': container.get('CreatedAt', ''),
                        'image': container.get('Image', ''),
                        'ports': container.get('Publishers', []) or []
                    }
            except json.JSONDecodeError as e:
                print(f"JSON parsing error: {e}")
    except Exception as e:
        print(f"Error getting container status: {e}")

    return container_data

def build_container(service_name):
    """Build specific container"""
    if service_name == 'panel':
        logger.warning("Attempted to build panel container; operation blocked")
        return False, "Cannot build panel container"
    try:
        project = get_compose_project()
        base = get_compose_cmd_base()
        resolved_file = get_resolved_compose_file()
        cmd = base + ['-f', resolved_file] + (['--project-name', project] if project else []) + ['build', service_name]
        logger.info(f"Building container: service={service_name}, cmd={' '.join(cmd)}")
        start_time = datetime.now()
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=300, cwd='/workspace/docker')  # 5 minutes timeout
        duration = (datetime.now() - start_time).total_seconds()
        logger.info(f"Build result: rc={result.returncode}, duration={duration}s, stdout='{result.stdout.strip()}', stderr='{result.stderr.strip()}'")
        
        if result.returncode == 0:
            return True, f"Container {service_name} built successfully"
        else:
            return False, f"Build failed: {result.stderr or result.stdout}"
    except subprocess.TimeoutExpired as te:
        logger.error(f"Build timeout for service={service_name}: {str(te)}")
        return False, "Build timeout (5 minutes)"
    except Exception as e:
        logger.exception(f"Build error for service={service_name}")
        return False, f"Build error: {str(e)}"

# Helper: detect compose project name from panel container labels
COMPOSE_CMD_BASE = None
COMPOSE_PROJECT_NAME = None

def get_compose_cmd_base():
    """Return the base compose command, preferring plugin, falling back to docker-compose."""
    global COMPOSE_CMD_BASE
    if COMPOSE_CMD_BASE:
        return COMPOSE_CMD_BASE
    # Try Docker Compose plugin: `docker compose`
    try:
        res = subprocess.run(['docker', 'compose', 'version'], capture_output=True, text=True, timeout=5)
        if res.returncode == 0:
            COMPOSE_CMD_BASE = ['docker', 'compose']
            logger.info("Using Docker Compose plugin: 'docker compose'")
            return COMPOSE_CMD_BASE
    except Exception as e:
        logger.info(f"'docker compose' not available: {e}")
    # Try docker-compose binary
    try:
        if shutil.which('docker-compose'):
            res2 = subprocess.run(['docker-compose', 'version'], capture_output=True, text=True, timeout=5)
            if res2.returncode == 0:
                COMPOSE_CMD_BASE = ['docker-compose']
                logger.info("Using docker-compose binary")
                return COMPOSE_CMD_BASE
    except Exception as e:
        logger.info(f"'docker-compose' not available: {e}")
    # Fallback to plugin even if version check failed
    COMPOSE_CMD_BASE = ['docker', 'compose']
    logger.warning("Compose not detected; defaulting to 'docker compose' which may fail")
    return COMPOSE_CMD_BASE

def get_compose_project():
    global COMPOSE_PROJECT_NAME
    if COMPOSE_PROJECT_NAME:
        return COMPOSE_PROJECT_NAME
    try:
        result = subprocess.run(
            ['docker', 'inspect', '-f', '{{ index .Config.Labels "com.docker.compose.project" }}', 'mlite_panel'],
            capture_output=True, text=True, timeout=5
        )
        if result.returncode == 0:
            name = result.stdout.strip()
            if name:
                COMPOSE_PROJECT_NAME = name
                logger.info(f"Detected compose project: {COMPOSE_PROJECT_NAME}")
                return COMPOSE_PROJECT_NAME
    except Exception as e:
        logger.warning(f"Failed to detect compose project name: {e}")
    return None

def start_container(service_name):
    """Start specific container"""
    if service_name == 'panel':
        logger.warning("Attempted to start panel container; operation blocked")
        return False, "Cannot control panel container"
    try:
        project = get_compose_project()
        base = get_compose_cmd_base()
        cmd = base + ['--env-file', '/workspace/docker/.env', '-f', '/workspace/docker/docker-compose.yml'] + (['--project-name', project] if project else []) + ['up', '-d', '--no-deps', service_name]
        logger.info(f"Starting container: service={service_name}, cmd={' '.join(cmd)}")
        start_time = datetime.now()
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=120, cwd='/workspace')  # 2 minutes timeout
        duration = (datetime.now() - start_time).total_seconds()
        logger.info(f"Start result: rc={result.returncode}, duration={duration}s, stdout='{result.stdout.strip()}', stderr='{result.stderr.strip()}'")
        
        if result.returncode == 0:
            return True, f"Container {service_name} started successfully"
        # Fallback: directly start container(s) by label
        logger.info(f"Compose up failed for service={service_name}, attempting docker start by labels")
        label_filters = ['--filter', f'label=com.docker.compose.service={service_name}']
        if project:
            label_filters += ['--filter', f'label=com.docker.compose.project={project}']
        ps_cmd = ['docker', 'ps', '-a', '-q'] + label_filters
        ps_res = subprocess.run(ps_cmd, capture_output=True, text=True, timeout=10)
        ids = [line.strip() for line in ps_res.stdout.splitlines() if line.strip()]
        logger.info(f"Fallback discovered {len(ids)} container(s) for service={service_name}: {ids}")
        if ids:
            start_cmd = ['docker', 'start'] + ids
            start_res = subprocess.run(start_cmd, capture_output=True, text=True, timeout=30)
            logger.info(f"Fallback start result: rc={start_res.returncode}, stdout='{start_res.stdout.strip()}', stderr='{start_res.stderr.strip()}'")
            if start_res.returncode == 0:
                return True, f"Container(s) for {service_name} started"
            else:
                return False, f"Fallback start failed: {start_res.stderr or start_res.stdout}"
        else:
            return False, f"Start failed: {result.stderr or result.stdout}"
    except subprocess.TimeoutExpired as te:
        logger.error(f"Start timeout for service={service_name}: {str(te)}")
        return False, "Start timeout (2 minutes)"
    except Exception as e:
        logger.exception(f"Start error for service={service_name}")
        return False, f"Start error: {str(e)}"

def stop_container(service_name):
    """Stop specific container"""
    if service_name == 'panel':
        logger.warning("Attempted to stop panel container; operation blocked")
        return False, "Cannot control panel container"
    try:
        project = get_compose_project()
        base = get_compose_cmd_base()
        cmd = base + ['--env-file', '/workspace/docker/.env', '-f', '/workspace/docker/docker-compose.yml'] + (['--project-name', project] if project else []) + ['stop', service_name]
        logger.info(f"Stopping container: service={service_name}, cmd={' '.join(cmd)}")
        start_time = datetime.now()
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=60, cwd='/workspace/docker')  # 1 minute timeout
        duration = (datetime.now() - start_time).total_seconds()
        logger.info(f"Stop result: rc={result.returncode}, duration={duration}s, stdout='{result.stdout.strip()}', stderr='{result.stderr.strip()}'")
        
        if result.returncode == 0:
            return True, f"Container {service_name} stopped successfully"
        # Fallback: directly stop container(s) by label
        logger.info(f"Compose stop failed for service={service_name}, attempting docker stop by labels")
        label_filters = ['--filter', f'label=com.docker.compose.service={service_name}']
        if project:
            label_filters += ['--filter', f'label=com.docker.compose.project={project}']
        ps_cmd = ['docker', 'ps', '-q'] + label_filters
        ps_res = subprocess.run(ps_cmd, capture_output=True, text=True, timeout=10)
        ids = [line.strip() for line in ps_res.stdout.splitlines() if line.strip()]
        logger.info(f"Fallback discovered {len(ids)} container(s) for service={service_name}: {ids}")
        if ids:
            stop_cmd = ['docker', 'stop'] + ids
            stop_res = subprocess.run(stop_cmd, capture_output=True, text=True, timeout=30)
            logger.info(f"Fallback stop result: rc={stop_res.returncode}, stdout='{stop_res.stdout.strip()}', stderr='{stop_res.stderr.strip()}'")
            if stop_res.returncode == 0:
                return True, f"Container(s) for {service_name} stopped"
            else:
                return False, f"Fallback stop failed: {stop_res.stderr or stop_res.stdout}"
        else:
            return False, f"Stop failed: {result.stderr or result.stdout}"
    except subprocess.TimeoutExpired as te:
        logger.error(f"Stop timeout for service={service_name}: {str(te)}")
        return False, "Stop timeout (1 minute)"
    except Exception as e:
        logger.exception(f"Stop error for service={service_name}")
        return False, f"Stop error: {str(e)}"

def restart_container(service_name):
    """Restart specific container"""
    try:
        project = get_compose_project()
        base = get_compose_cmd_base()
        cmd = base + ['--env-file', '/workspace/docker/.env', '-f', '/workspace/docker/docker-compose.yml'] + (['--project-name', project] if project else []) + ['restart', service_name]
        logger.info(f"Restarting container: service={service_name}, cmd={' '.join(cmd)}")
        start_time = datetime.now()
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=120, cwd='/workspace/docker')  # 2 minutes timeout
        duration = (datetime.now() - start_time).total_seconds()
        logger.info(f"Restart result: rc={result.returncode}, duration={duration}s, stdout='{result.stdout.strip()}', stderr='{result.stderr.strip()}'")
        
        if result.returncode == 0:
            return True, f"Container {service_name} restarted successfully"
        else:
            return False, f"Restart failed: {result.stderr or result.stdout}"
    except subprocess.TimeoutExpired as te:
        logger.error(f"Restart timeout for service={service_name}: {str(te)}")
        return False, "Restart timeout (2 minutes)"
    except Exception as e:
        logger.exception(f"Restart error for service={service_name}")
        return False, f"Restart error: {str(e)}"

def check_nginx_status():
    """Check if nginx container is running (via docker inspect), fallback to process check"""
    try:
        result = subprocess.run(
            ['docker', 'inspect', '-f', '{{.State.Running}}', NGINX_CONTAINER_NAME],
            capture_output=True, text=True
        )
        if result.returncode == 0:
            return result.stdout.strip().lower() == 'true'
    except Exception:
        pass
    # Fallback: try to detect nginx process inside the container without requiring pgrep
    try:
        result2 = subprocess.run(
            ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', "ps | grep nginx | grep -v grep"],
            capture_output=True, text=True
        )
        return result2.returncode == 0 and result2.stdout.strip() != ''
    except Exception:
        return False

# Simple login-required decorator
def login_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if not session.get('logged_in'):
            next_url = request.path if request.method == 'GET' else None
            return redirect(url_for('login', next=next_url))
        return f(*args, **kwargs)
    return decorated_function

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form.get('username', '').strip()
        password = request.form.get('password', '').strip()
        if not username or not password:
            flash('Username dan password wajib diisi', 'error')
            return render_template('login.html')
        if username == app.config.get('ADMIN_USER') and password == app.config.get('ADMIN_PASS'):
            session['logged_in'] = True
            session['username'] = username
            next_url = request.args.get('next')
            if next_url and next_url.startswith('/'):
                return redirect(next_url)
            return redirect(url_for('dashboard'))
        else:
            flash('Username atau password salah', 'error')
            return render_template('login.html')
    if session.get('logged_in'):
        return redirect(url_for('dashboard'))
    return render_template('login.html')

@app.route('/logout')
def logout():
    session.clear()
    flash('Anda telah logout', 'success')
    return redirect(url_for('login'))

@app.route('/')
@login_required
def dashboard():
    """Dashboard page showing all sites - HOT RELOAD TEST"""
    sites = get_sites()
    nginx_status = check_nginx_status()
    return render_template('dashboard.html', sites=sites, nginx_status=nginx_status)

@app.route('/add-site', methods=['GET', 'POST'])
@login_required
def add_site():
    nginx_status = check_nginx_status()
    """Add new site page"""
    if request.method == 'POST':
        domain = request.form.get('domain', '').strip()
        site_type = request.form.get('site_type', 'proxy').strip()
        port = request.form.get('port', '').strip()
        root_dir = request.form.get('root_dir', '/var/www/public').strip()
        php_version = request.form.get('php_version', '8.3').strip()
        
        # Validation
        if not domain:
            flash('Domain is required', 'error')
            return render_template('add_site.html', nginx_status=nginx_status)
        
        if site_type == 'proxy':
            if not port:
                flash('Port is required for proxy sites', 'error')
                return render_template('add_site.html', nginx_status=nginx_status)
            try:
                port_int = int(port)
                if port_int < 1 or port_int > 65535:
                    flash('Port must be between 1 and 65535', 'error')
                    return render_template('add_site.html', nginx_status=nginx_status)
            except ValueError:
                flash('Port must be a valid number', 'error')
                return render_template('add_site.html', nginx_status=nginx_status)
        elif site_type == 'php':
            if not root_dir:
                flash('Root directory is required for PHP-FPM sites', 'error')
                return render_template('add_site.html', nginx_status=nginx_status)
            # Validate and ensure directory within /var/www/public
            try:
                safe_root = safe_join(PHP_WEBROOT_BASE, root_dir)
            except ValueError as ve:
                flash(str(ve), 'error')
                return render_template('add_site.html', nginx_status=nginx_status)
            ok, msg = ensure_directory(safe_root)
            if not ok:
                flash(msg, 'error')
                return render_template('add_site.html', nginx_status=nginx_status)
            # Use the safe normalized root in config
            root_dir = safe_root
            # Optionally create default index.html (non-blocking if fails)
            if CREATE_DEFAULT_INDEX:
                ok_idx, msg_idx = create_default_index(root_dir, domain)
                if not ok_idx:
                    flash(f'Peringatan: {msg_idx}', 'warning')
        elif site_type == 'static':
            if not root_dir:
                flash('Root directory is required for Static sites', 'error')
                return render_template('add_site.html', nginx_status=nginx_status)
            try:
                safe_root = safe_join(PHP_WEBROOT_BASE, root_dir)
            except ValueError as ve:
                flash(str(ve), 'error')
                return render_template('add_site.html', nginx_status=nginx_status)
            ok, msg = ensure_directory(safe_root)
            if not ok:
                flash(msg, 'error')
                return render_template('add_site.html', nginx_status=nginx_status)
            root_dir = safe_root
            if CREATE_DEFAULT_INDEX:
                ok_idx, msg_idx = create_default_index(root_dir, domain)
                if not ok_idx:
                    flash(f'Peringatan: {msg_idx}', 'warning')
        else:
            flash('Invalid site type', 'error')
            return render_template('add_site.html', nginx_status=nginx_status)

        # Check if domain already exists
        existing_sites = get_sites()
        if any(site['domain'] == domain for site in existing_sites):
            flash('Domain already exists', 'error')
            return render_template('add_site.html', nginx_status=nginx_status)
        
        # Create nginx config
        created = False
        if site_type == 'php':
            created = create_nginx_config(domain, site_type='php', root_dir=root_dir, php_version=php_version)
        elif site_type == 'static':
            created = create_nginx_config(domain, site_type='static', root_dir=root_dir)
        else:
            created = create_nginx_config(domain, site_type='proxy', port=port)
        
        if created:
            # Reload nginx
            success, message = reload_nginx()
            if success:
                flash('Site added successfully', 'success')
                return redirect(url_for('dashboard'))
            else:
                # If reload fails, delete the config file
                delete_nginx_config(domain)
                flash(f'Failed to reload nginx: {message}', 'error')
        else:
            flash('Failed to create configuration file', 'error')
            # Fallthrough to final render
    
    return render_template('add_site.html', nginx_status=nginx_status)

@app.route('/delete-site/<domain>')
@login_required
def delete_site(domain):
    """Delete site configuration"""
    if delete_nginx_config(domain):
        success, message = reload_nginx()
        if success:
            flash('Site deleted successfully', 'success')
        else:
            flash(f'Site deleted but nginx reload failed: {message}', 'warning')
    else:
        flash('Failed to delete site configuration', 'error')
    
    return redirect(url_for('dashboard'))

@app.route('/api/sites')
@login_required
def api_sites():
    """API endpoint to get all sites"""
    sites = get_sites()
    return jsonify({
        'sites': sites,
        'total': len(sites)
    })

@app.route('/api/nginx-status')
@login_required
def api_nginx_status():
    """API endpoint for nginx status"""
    sites = get_sites()
    nginx_running = check_nginx_status()
    
    return jsonify({
        'status': 'running' if nginx_running else 'stopped',
        'total_sites': len(sites),
        'sites': sites
    })

@app.route('/api/network-stats')
@login_required
def api_network_stats():
    """API endpoint to get network stats for nginx container (mlite_nginx)."""
    try:
        series = get_container_network_stats()
        return jsonify({
            'success': True,
            'network': series
        })
    except Exception as e:
        logger.error(f"Error in /api/network-stats: {e}")
        return jsonify({
            'success': False,
            'error': str(e),
            'network': []
        }), 500

@app.route('/api/reload-nginx')
@login_required
def api_reload_nginx():
    """API endpoint to reload nginx"""
    success, message = reload_nginx()
    return jsonify({
        'success': success,
        'message': message
    })

# Helpers for vhost .conf editing
CONF_MAX_SIZE = 200 * 1024  # 200KB safety limit
SITE_NAME_REGEX = re.compile(r'^[a-zA-Z0-9.-]+$')

def is_valid_site_name(name: str) -> bool:
    return bool(name) and SITE_NAME_REGEX.match(name) is not None and '/' not in name and '\\' not in name

def get_conf_path(site_name: str) -> str:
    filename = f"{site_name}.conf"
    path = os.path.normpath(os.path.join(NGINX_CONF_DIR, filename))
    base_norm = os.path.normpath(NGINX_CONF_DIR)
    if not path.startswith(base_norm):
        raise ValueError('Invalid path resolved')
    return path

def read_conf_file(site_name: str) -> tuple[bool, str, str]:
    try:
        if not is_valid_site_name(site_name):
            return False, '', 'Invalid site name'
        path = get_conf_path(site_name)
        if not os.path.exists(path):
            return False, '', 'Configuration file not found'
        with open(path, 'r') as f:
            content = f.read()
        return True, content, ''
    except Exception as e:
        return False, '', str(e)

def backup_conf_file(site_name: str) -> tuple[bool, str]:
    try:
        path = get_conf_path(site_name)
        ts = datetime.now().strftime('%Y%m%d_%H%M%S')
        backup_path = f"{path}.bak_{ts}"
        shutil.copyfile(path, backup_path)
        return True, backup_path
    except Exception as e:
        return False, str(e)

def write_conf_file(site_name: str, content: str) -> tuple[bool, str]:
    try:
        if len(content.encode('utf-8')) > CONF_MAX_SIZE:
            return False, 'Configuration too large'
        path = get_conf_path(site_name)
        with open(path, 'w') as f:
            f.write(content)
        return True, ''
    except Exception as e:
        return False, str(e)

@app.route('/edit_vhost/<site_name>', methods=['GET', 'POST'])
@login_required
def edit_vhost(site_name):
    nginx_status = check_nginx_status()
    if not is_valid_site_name(site_name):
        flash('Invalid site name', 'error')
        return redirect(url_for('dashboard'))
    if request.method == 'GET':
        ok, content, err = read_conf_file(site_name)
        if not ok:
            flash(err, 'error')
            return redirect(url_for('dashboard'))
        return render_template('edit_vhost.html', site_name=site_name, content=content, nginx_status=nginx_status)
    # POST: save changes with backup and reload nginx
    new_content = request.form.get('content', '')
    if not new_content:
        flash('Content cannot be empty', 'error')
        return redirect(url_for('edit_vhost', site_name=site_name))
    # Backup first
    b_ok, backup_path_or_err = backup_conf_file(site_name)
    if not b_ok:
        flash(f'Backup failed: {backup_path_or_err}', 'error')
        return redirect(url_for('edit_vhost', site_name=site_name))
    # Write file
    w_ok, w_err = write_conf_file(site_name, new_content)
    if not w_ok:
        flash(f'Write failed: {w_err}', 'error')
        return redirect(url_for('edit_vhost', site_name=site_name))
    # Test and reload
    success, message = reload_nginx()
    if success:
        flash('Vhost configuration saved and Nginx reloaded successfully', 'success')
        return redirect(url_for('dashboard'))
    else:
        # Revert from backup on failure
        try:
            shutil.copyfile(backup_path_or_err, get_conf_path(site_name))
            reload_nginx()
        except Exception:
            pass
        flash(f'Nginx test/reload failed: {message}. Changes reverted.', 'error')
        return redirect(url_for('edit_vhost', site_name=site_name))
    return redirect(url_for('dashboard'))

@app.route('/containers')
@login_required
def containers():
    """Container management page"""
    print("[DEBUG] === CONTAINERS ROUTE STARTED ===")
    nginx_status = check_nginx_status()

    services = get_services_from_compose()
    print(f"[DEBUG] get_services_from_compose() returned {len(services)} services")

    # Prefer docker ps-based status (works without compose plugin)
    docker_ps_status = get_container_status_via_docker_ps()
    print(f"[DEBUG] get_container_status_via_docker_ps() returned {len(docker_ps_status)} items")

    containers_data = []
    print(f"[DEBUG] Processing {len(services)} services...")
    for service in services:
        service_name = service['name']
        container_name = service['container_name']
        container_info = docker_ps_status.get(container_name, {
            'running': False,
            'status': 'not created',
            'created': '',
            'image': service['image'],
            'ports': []
        })
        containers_data.append({
            'name': container_name,
            'service': service_name,
            'running': container_info['running'],
            'status': container_info['status'],
            'created': container_info['created'],
            'image': container_info['image'],
            'ports': deduplicate_ports(container_info['ports']),
            'build': service['build'],
            'volumes': service['volumes'],
            'environment': service['environment'],
            'depends_on': service['depends_on']
        })

    print(f"[DEBUG] Final containers_data: {len(containers_data)} items")

    categories = categorize_services(containers_data)
    print(f"[DEBUG] Created {len(categories)} categories: {list(categories.keys())}")
    for cat_name, cat_items in categories.items():
        print(f"[DEBUG] Category '{cat_name}': {len(cat_items)} items")

    print("[DEBUG] === CONTAINERS ROUTE COMPLETED ===")
    return render_template('containers.html', categories=categories, containers=containers_data, nginx_status=nginx_status)

@app.route('/container/build/<service_name>', methods=['POST'])
@login_required
def container_build(service_name):
    """Build specific container"""
    # Security: validate service name
    if not re.match(r'^[a-zA-Z0-9_-]+$', service_name):
        flash('Invalid service name', 'error')
        return redirect(url_for('containers'))
    
    success, message = build_container(service_name)
    if success:
        flash(message, 'success')
    else:
        flash(message, 'error')
    
    return redirect(url_for('containers'))

@app.route('/container/build-log/<service_name>')
@login_required
def container_build_log(service_name):
    """Stream build logs for specific service via SSE and persist to temp file"""
    # Validate service name
    if not re.match(r'^[a-zA-Z0-9_-]+$', service_name):
        return Response("data: Invalid service name\n\n", mimetype='text/event-stream')
    if service_name == 'panel':
        return Response("data: Cannot build panel container\n\n", mimetype='text/event-stream')

    project = get_compose_project()
    base = get_compose_cmd_base()
    resolved_file = get_resolved_compose_file()
    cmd = base + ['-f', resolved_file] + (['--project-name', project] if project else []) + ['build', service_name]

    # Prepare temp log file path
    log_dir = '/app/tmp'
    try:
        os.makedirs(log_dir, exist_ok=True)
    except Exception:
        pass
    log_path = os.path.join(log_dir, f"build_{service_name}_{int(time.time())}.log")

    def generate():
        yield f"data: Running: {' '.join(cmd)}\n\n"
        try:
            with open(log_path, 'w') as lf:
                lf.write(f"Running: {' '.join(cmd)}\n")
                ensure_compose_env()
                env = os.environ.copy()
                proj_dir = get_project_dir()
                if proj_dir:
                    env['HOST_PROJECT_DIR'] = proj_dir
                proc = subprocess.Popen(
                    cmd,
                    cwd='/workspace/docker',
                    env=env,
                    stdout=subprocess.PIPE,
                    stderr=subprocess.STDOUT,
                    text=True,
                    bufsize=1
                )
                for line in proc.stdout:
                    clean = line.rstrip()
                    lf.write(clean + "\n")
                    lf.flush()
                    yield f"data: {clean}\n\n"
                proc.wait()
                lf.write(f"Build finished with code {proc.returncode}\n")
                lf.flush()
                yield f"data: Build finished with code {proc.returncode}\n\n"
                yield "event: done\n" + ("data: success\n\n" if proc.returncode == 0 else "data: failed\n\n")
        except Exception as e:
            try:
                with open(log_path, 'a') as lf:
                    lf.write(f"Build error: {str(e)}\n")
            except Exception:
                pass
            yield f"data: Build error: {str(e)}\n\n"
            yield "event: done\ndata: error\n\n"

    return Response(stream_with_context(generate()), mimetype='text/event-stream')

@app.route('/container/up/<service_name>', methods=['POST'])
@login_required
def container_up(service_name):
    """Start specific container"""
    if not re.match(r'^[a-zA-Z0-9_-]+$', service_name):
        flash('Invalid service name', 'error')
        return redirect(url_for('containers'))
    
    success, message = start_container(service_name)
    if success:
        flash(message, 'success')
    else:
        flash(message, 'error')
    
    return redirect(url_for('containers'))

@app.route('/container/down/<service_name>', methods=['POST'])
@login_required
def container_down(service_name):
    """Stop specific container"""
    logger.info(f"[container_down] POST received for service='{service_name}'")
    if not re.match(r'^[a-zA-Z0-9_-]+$', service_name):
        logger.warning(f"[container_down] Invalid service name: '{service_name}'")
        flash('Invalid service name', 'error')
        return redirect(url_for('containers'))
    
    success, message = stop_container(service_name)
    logger.info(f"[container_down] stop_container returned success={success}, message='{message}'")
    if success:
        flash(message, 'success')
    else:
        # Tampilkan pesan error dan arahkan user untuk melihat logs
        if 'Cannot control panel container' in message:
            flash('Tidak dapat menghentikan container panel dari UI.', 'error')
        else:
            flash(f"Gagal stop: {message}. Lihat logs untuk detail.", 'error')
    
    return redirect(url_for('containers'))



def categorize_services(containers):
    """Categorize services into groups"""
    categories = {
        'PHP Containers': [],
        'Web Services': [],
        'Database & Services': []
    }
    for container in containers:
        service_name = container['service']
        if service_name.startswith('php'):
            categories['PHP Containers'].append(container)
        elif service_name in ['nginx', 'panel']:
            categories['Web Services'].append(container)
        else:
            categories['Database & Services'].append(container)
    return categories

# Add SSE start-log route
@app.route('/container/start-log/<service_name>')
@login_required
def container_start_log(service_name):
    """Stream start (compose up) logs for specific service via SSE."""
    if not re.match(r'^[a-zA-Z0-9_-]+$', service_name):
        return Response("data: Invalid service name\n\n", mimetype='text/event-stream')
    if service_name == 'panel':
        return Response("data: Cannot control panel container\n\n", mimetype='text/event-stream')

    project = get_compose_project()
    base = get_compose_cmd_base()
    resolved_file = get_resolved_compose_file()
    cmd = base + ['-f', resolved_file] + (['--project-name', project] if project else []) + ['up', '-d', '--no-deps', service_name]

    log_dir = '/app/tmp'
    try:
        os.makedirs(log_dir, exist_ok=True)
    except Exception:
        pass
    log_path = os.path.join(log_dir, f"start_{service_name}_{int(time.time())}.log")

    def generate():
        yield f"data: Running: {' '.join(cmd)}\n\n"
        try:
            with open(log_path, 'w') as lf:
                lf.write(f"Running: {' '.join(cmd)}\n")
                env = os.environ.copy()
                proj_dir = get_project_dir()
                if proj_dir:
                    env['HOST_PROJECT_DIR'] = proj_dir
                proc = subprocess.Popen(
                    cmd,
                    cwd='/workspace/docker',
                    stdout=subprocess.PIPE,
                    stderr=subprocess.STDOUT,
                    text=True,
                    bufsize=1
                )
                for line in proc.stdout:
                    if line:
                        lf.write(line)
                        lf.flush()
                        yield f"data: {line.strip()}\n\n"
                rc = proc.wait()
                lf.write(f"\nStart finished with code {rc}\n")
                yield "event: done\n"
                yield f"data: {'success' if rc == 0 else 'error'}\n\n"
        except Exception as e:
            try:
                with open(log_path, 'a') as lf:
                    lf.write(f"\nStart error: {str(e)}\n")
            except Exception:
                pass
            yield f"data: Start error: {str(e)}\n\n"
            yield "event: done\n"
            yield "data: error\n\n"

    return Response(stream_with_context(generate()), mimetype='text/event-stream')

@app.route('/nginx')
@login_required
def nginx_page():
    """Nginx management page"""
    try:
        # Get nginx container info
        docker_ps_status = get_container_status_via_docker_ps()
        nginx_info = docker_ps_status.get(NGINX_CONTAINER_NAME, {
            'running': False,
            'status': 'not found',
            'image': 'unknown',
            'ports': [],
            'created': ''
        })
        # Format ports for display
        try:
            ports_list = deduplicate_ports(nginx_info.get('ports', []) or [])
            formatted = []
            for p in ports_list:
                pub = str(p.get('PublishedPort')) if p.get('PublishedPort') is not None else ''
                tgt = str(p.get('TargetPort')) if p.get('TargetPort') is not None else ''
                if pub and tgt:
                    formatted.append(f"{pub}:{tgt}")
                elif tgt:
                    formatted.append(tgt)
            nginx_ports_display = ', '.join(formatted)
        except Exception:
            nginx_ports_display = ''
        
        # Get nginx configuration files (enabled and disabled)
        config_files = []
        if os.path.exists(NGINX_CONF_DIR):
            for filename in os.listdir(NGINX_CONF_DIR):
                if filename.endswith('.conf') or filename.endswith('.conf.disabled'):
                    filepath = os.path.join(NGINX_CONF_DIR, filename)
                    try:
                        with open(filepath, 'r') as f:
                            content = f.read()
                        # Parse config info
                        enabled = filename.endswith('.conf')
                        domain = filename.replace('.conf.disabled', '').replace('.conf', '')
                        site_type = 'proxy'
                        port = None
                        root_dir = None
                        if 'fastcgi_pass' in content:
                            site_type = 'php'
                        elif 'proxy_pass' in content:
                            site_type = 'proxy'
                            for line in content.split('\n'):
                                if 'proxy_pass http://localhost:' in line:
                                    port = line.split('proxy_pass http://localhost:')[1].split(';')[0].strip()
                                    break
                        else:
                            site_type = 'static'
                        config_files.append({
                            'domain': domain,
                            'type': site_type,
                            'port': port,
                            'root_dir': root_dir,
                            'filename': filename,
                            'created': datetime.fromtimestamp(os.path.getctime(filepath)).strftime('%Y-%m-%d %H:%M:%S'),
                            'enabled': enabled
                        })
                    except Exception as e:
                        print(f"Error reading config file {filename}: {e}")
        
        # Get nginx logs
        logs = []
        try:
            # Get last 50 lines of nginx logs
            result = subprocess.run([
                'docker', 'logs', '--tail', '50', NGINX_CONTAINER_NAME
            ], capture_output=True, text=True, timeout=10)
            
            if result.returncode == 0:
                logs = result.stdout.strip().split('\n')[-20:]  # Last 20 lines
        except Exception as e:
            print(f"Error getting nginx logs: {e}")
        
        return render_template('nginx.html',
                             nginx_info=nginx_info,
                             nginx_ports_display=nginx_ports_display,
                             config_files=config_files,
                             logs=logs,
                             nginx_container_name=NGINX_CONTAINER_NAME)
    except Exception as e:
        flash(f'Error loading nginx page: {str(e)}', 'error')
        return redirect(url_for('dashboard'))

@app.route('/container/restart/<service_name>', methods=['POST'])
@login_required
def container_restart(service_name):
    """Restart specific container"""
    if not re.match(r'^[a-zA-Z0-9_-]+$', service_name):
        flash('Invalid service name', 'error')
        return redirect(url_for('containers'))
    
    success, message = restart_container(service_name)
    if success:
        flash(message, 'success')
    else:
        flash(message, 'error')
    
    return redirect(url_for('containers'))

def categorize_services(containers):
    """Categorize services into groups"""
    categories = {
        'PHP Containers': [],
        'Web Services': [],
        'Database & Services': []
    }
    for container in containers:
        service_name = container['service']
        if service_name.startswith('php'):
            categories['PHP Containers'].append(container)
        elif service_name in ['nginx', 'panel']:
            categories['Web Services'].append(container)
        else:
            categories['Database & Services'].append(container)
    return categories

def get_resolved_compose_file():
    """Generate a resolved compose file by parsing YAML and converting
    ${HOST_PROJECT_DIR}, ./ and ../ host paths in volumes to absolute host paths.
    Returns path to the temporary resolved compose file.
    """
    try:
        src_path = '/workspace/docker/docker-compose.yml'
        if not os.path.exists(src_path):
            return src_path
        with open(src_path, 'r') as f:
            data = yaml.safe_load(f)
        host_dir = get_project_dir()
        if not isinstance(data, dict):
            raise Exception('compose data invalid')
        services = data.get('services', {})
        compose_dir_host = os.path.join(host_dir, 'docker') if host_dir else None
        for svc_name, svc in (services or {}).items():
            vols = svc.get('volumes')
            if not vols:
                continue
            new_vols = []
            for v in vols:
                if isinstance(v, str):
                    parts = v.split(':')
                    if len(parts) >= 2:
                        src = parts[0]
                        rest = ':'.join(parts[1:])
                        # Convert only host-side src
                        if host_dir:
                            if src == '${HOST_PROJECT_DIR}':
                                src = host_dir
                            elif src.startswith('./') and compose_dir_host:
                                src = os.path.join(compose_dir_host, src[2:])
                            elif src.startswith('../'):
                                src = os.path.join(host_dir, src[3:])
                        new_v = f"{src}:{rest}"
                        new_vols.append(new_v)
                    else:
                        new_vols.append(v)
                else:
                    new_vols.append(v)
            svc['volumes'] = new_vols
        out_path = '/workspace/docker/.compose-resolved.yml'
        with open(out_path, 'w') as f:
            f.write(yaml.safe_dump(data, sort_keys=False))
        return out_path
    except Exception:
        return '/workspace/docker/docker-compose.yml'

@app.route('/nginx/toggle/<domain>', methods=['POST'])
@login_required
def toggle_site(domain):
    """Enable/Disable a site's nginx config by renaming .conf <-> .conf.disabled"""
    try:
        if not is_valid_site_name(domain):
            flash('Invalid domain name', 'error')
            return redirect(url_for('nginx_page'))
        conf_path = get_conf_path(domain)
        disabled_path = os.path.normpath(os.path.join(NGINX_CONF_DIR, f"{domain}.conf.disabled"))
        # Determine current state
        enabled = os.path.exists(conf_path)
        if enabled:
            # Disable: move to .conf.disabled
            try:
                shutil.move(conf_path, disabled_path)
            except Exception as e:
                flash(f'Failed to disable site: {str(e)}', 'error')
                return redirect(url_for('nginx_page'))
            success, message = reload_nginx()
            if not success:
                # revert
                try:
                    shutil.move(disabled_path, conf_path)
                except Exception:
                    pass
                flash(f'Failed to reload nginx: {message}', 'error')
            else:
                flash('Site disabled and nginx reloaded', 'success')
        else:
            # Enable: move .conf.disabled back to .conf
            if not os.path.exists(disabled_path):
                flash('Disabled config not found', 'error')
                return redirect(url_for('nginx_page'))
            try:
                shutil.move(disabled_path, conf_path)
            except Exception as e:
                flash(f'Failed to enable site: {str(e)}', 'error')
                return redirect(url_for('nginx_page'))
            success, message = reload_nginx()
            if not success:
                # revert
                try:
                    shutil.move(conf_path, disabled_path)
                except Exception:
                    pass
                flash(f'Failed to reload nginx: {message}', 'error')
            else:
                flash('Site enabled and nginx reloaded', 'success')
    except Exception as e:
        flash(f'Error toggling site: {str(e)}', 'error')
    return redirect(url_for('nginx_page'))

@app.route('/nginx/delete/<domain>', methods=['POST'])
@login_required
def nginx_delete_site(domain):
    """Delete a site's nginx config (.conf or .conf.disabled) and reload nginx"""
    try:
        if not is_valid_site_name(domain):
            flash('Invalid domain name', 'error')
            return redirect(url_for('nginx_page'))
        conf_path = get_conf_path(domain)
        disabled_path = os.path.normpath(os.path.join(NGINX_CONF_DIR, f"{domain}.conf.disabled"))
        removed = False
        # Try remove enabled .conf
        if os.path.exists(conf_path):
            try:
                os.remove(conf_path)
                removed = True
            except Exception as e:
                flash(f'Failed to delete config: {str(e)}', 'error')
                return redirect(url_for('nginx_page'))
        # Or remove disabled file
        elif os.path.exists(disabled_path):
            try:
                os.remove(disabled_path)
                removed = True
            except Exception as e:
                flash(f'Failed to delete disabled config: {str(e)}', 'error')
                return redirect(url_for('nginx_page'))
        else:
            flash('Config file not found', 'error')
            return redirect(url_for('nginx_page'))
        # Reload nginx if removed
        if removed:
            success, message = reload_nginx()
            if success:
                flash('Config deleted and nginx reloaded', 'success')
            else:
                flash(f'Config deleted but reload failed: {message}', 'warning')
    except Exception as e:
        flash(f'Error deleting site: {str(e)}', 'error')
    return redirect(url_for('nginx_page'))

# PHP-FPM Routes
@app.route('/php-fpm')
@login_required
def php_fpm():
    """PHP-FPM management page"""
    nginx_status = check_nginx_status()
    php_containers = get_php_containers()
    return render_template('php_fpm.html', php_containers=php_containers, nginx_status=nginx_status)

@app.route('/api/php-containers')
@login_required
def api_php_containers():
    """API endpoint to get PHP containers status"""
    try:
        containers = get_php_containers()
        return jsonify({
            'success': True,
            'containers': containers
        })
    except Exception as e:
        logger.error(f"Error getting PHP containers: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

# MySQL Routes
@app.route('/mysql')
@login_required
def mysql():
    """MySQL management page"""
    nginx_status = check_nginx_status()
    mysql_containers = get_mysql_containers()
    return render_template('mysql.html', mysql_containers=mysql_containers, nginx_status=nginx_status)

@app.route('/api/mysql-containers')
@login_required
def api_mysql_containers():
    """API endpoint to get MySQL containers status"""
    try:
        containers = get_mysql_containers()
        return jsonify({
            'success': True,
            'containers': containers
        })
    except Exception as e:
        logger.error(f"Error getting MySQL containers: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/files')
@login_required
def files():
    """Files management page"""
    nginx_status = check_nginx_status()
    return render_template('files.html', nginx_status=nginx_status)

@app.route('/api/files', methods=['GET'])
@login_required
def list_files():
    base_path = request.args.get('path', '/')
    # Normalize path to prevent directory traversal and ensure absolute path inside container
    base_path = os.path.normpath(base_path)
    if base_path.startswith('..') or not base_path.startswith('/'):
        return jsonify({'error': 'Invalid path'}), 400

    try:
        # Use docker exec to list files inside the Nginx container with detailed metadata
        quoted = shlex.quote(base_path)
        cmd = [
            'docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c',
            (
                'p=' + quoted + ' ; '\
                'if [ ! -d "$p" ]; then echo "__ERROR__|Not a directory"; exit 2; fi; '\
                'for f in "$p"/.* "$p"/*; do '\
                '  bn="$(basename "$f")"; '\
                '  [ "$bn" = "." ] && continue; [ "$bn" = ".." ] && continue; '\
                '  [ ! -e "$f" ] && continue; '\
                '  stat -c "%n|%F|%s|%Y|%a|%A|%U|%G" "$f" || true; '\
                'done'
            )
        ]
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=15)
        if result.returncode != 0:
            err = (result.stderr or result.stdout or '').strip()
            return jsonify({'error': f'Container access failed: {err}'}), 400

        files = []
        lines = (result.stdout or '').splitlines()
        for ln in lines:
            ln = ln.strip()
            if not ln:
                continue
            if ln.startswith('__ERROR__|'):
                # Path not a directory
                return jsonify({'error': ln.split('|', 1)[1]}), 400
            parts = ln.split('|')
            if len(parts) != 8:
                continue
            full_path, ftype, size, modified, mode, permissions, owner, group = parts
            name = os.path.basename(full_path)
            if name in ('.', '..'):
                continue
            is_dir = ftype.lower().startswith('directory')
            file_info = {
                'name': name,
                'path': os.path.normpath(os.path.join(base_path, name)),
                'is_dir': is_dir,
                'size': None if is_dir else int(size),
                'modified': int(modified),
                'permissions': permissions,
                'owner': owner,
                'group': group,
                'mode': mode
            }
            files.append(file_info)
        return jsonify(files)
    except Exception as e:
        return jsonify({'error': f'Could not access directory in container: {str(e)}'}), 400

# Backup and restore endpoints removed

@app.route('/api/files/upload', methods=['POST'])
@login_required
def upload_file():
    logger.info('File upload attempt')
    from werkzeug.utils import secure_filename
    import tempfile
    
    # Handle both single and multiple file uploads
    files = request.files.getlist('files') if 'files' in request.files else []
    if not files and 'file' in request.files:
        files = [request.files['file']]
    
    if not files:
        return jsonify({'error': 'No files selected'}), 400
    
    upload_path = request.form.get('path', '/')
    upload_path = os.path.normpath(upload_path)
    if upload_path.startswith('..') or not upload_path.startswith('/'):
        return jsonify({'error': 'Invalid path'}), 400

    try:
        uploaded_files = []
        failed_files = []
        
        for file in files:
            if file.filename == '':
                continue
            
            try:
                filename = secure_filename(file.filename)
                target_path = os.path.normpath(os.path.join(upload_path, filename))
                
                # Handle duplicate filenames inside container
                counter = 1
                original_filename = filename
                while True:
                    q = shlex.quote(target_path)
                    check_cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"[ -e {q} ] && echo EXISTS || true"]
                    chk = subprocess.run(check_cmd, capture_output=True, text=True)
                    if 'EXISTS' in (chk.stdout or ''):
                        name, ext = os.path.splitext(original_filename)
                        filename = f"{name}_{counter}{ext}"
                        target_path = os.path.normpath(os.path.join(upload_path, filename))
                        counter += 1
                    else:
                        break
                    
                # Save to temp file in panel container
                temp_dir = tempfile.mkdtemp(prefix='panel_upload_')
                temp_path = os.path.join(temp_dir, filename)
                file.save(temp_path)
                
                # Ensure destination directory exists inside container
                dest_dir = os.path.dirname(target_path) or '/'
                dest_q = shlex.quote(dest_dir)
                mkcmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"mkdir -p {dest_q}"]
                mk = subprocess.run(mkcmd, capture_output=True, text=True)
                if mk.returncode != 0:
                    raise Exception((mk.stderr or mk.stdout or 'mkdir failed').strip())
                
                # Copy file into container
                cp_cmd = ['docker', 'cp', temp_path, f"{NGINX_CONTAINER_NAME}:{target_path}"]
                cp = subprocess.run(cp_cmd, capture_output=True, text=True)
                
                # Cleanup temp
                try:
                    os.remove(temp_path)
                    os.rmdir(temp_dir)
                except Exception:
                    pass
                
                if cp.returncode != 0:
                    raise Exception((cp.stderr or cp.stdout or 'docker cp failed').strip())
                
                uploaded_files.append({
                    'original_name': file.filename,
                    'saved_name': filename,
                    'path': target_path
                })
                logger.info(f'File uploaded successfully: {target_path}')
                
            except Exception as e:
                failed_files.append({
                    'filename': file.filename,
                    'error': str(e)
                })
                logger.error(f'Failed to upload {file.filename}: {str(e)}')
        
        response_data = {
            'uploaded_files': uploaded_files,
            'failed_files': failed_files,
            'total_uploaded': len(uploaded_files),
            'total_failed': len(failed_files)
        }
        
        if uploaded_files:
            response_data['message'] = f'Successfully uploaded {len(uploaded_files)} file(s)'
            if failed_files:
                response_data['message'] += f', {len(failed_files)} file(s) failed'
        else:
            response_data['message'] = 'No files were uploaded'
            
        return jsonify(response_data)
        
    except Exception as e:
        logger.error(f'Upload error: {str(e)}')
        return jsonify({'error': f'Could not upload files: {str(e)}'}), 400

@app.route('/api/files/delete', methods=['POST'])
@login_required
def delete_file():
    logger.info('File deletion attempt')
    path = request.json.get('path')
    if not path:
        return jsonify({'error': 'No path specified'}), 400
    
    path = os.path.normpath(path)
    if path.startswith('..') or not path.startswith('/'):
        return jsonify({'error': 'Invalid path'}), 400
    
    try:
        q = shlex.quote(path)
        cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"if [ -e {q} ]; then if [ -d {q} ]; then rm -rf {q}; else rm -f {q}; fi; else echo NOTFOUND; fi"]
        res = subprocess.run(cmd, capture_output=True, text=True)
        out = (res.stdout or '').strip()
        if 'NOTFOUND' in out:
            return jsonify({'error': 'File or directory not found'}), 404
        if res.returncode != 0:
            return jsonify({'error': (res.stderr or res.stdout or 'Delete failed').strip()}), 400
        logger.info(f'Deleted successfully in container: {path}')
        return jsonify({'message': 'File/directory deleted successfully'})
    except Exception as e:
        return jsonify({'error': f'Could not delete: {str(e)}'}), 400

@app.route('/api/files/chmod', methods=['POST'])
@login_required
def chmod_file():
    logger.info('File chmod attempt')
    path = request.json.get('path')
    mode = request.json.get('mode')
    if not path or mode is None:
        return jsonify({'error': 'Path and mode must be specified'}), 400
    
    path = os.path.normpath(path)
    if path.startswith('..') or not path.startswith('/'):
        return jsonify({'error': 'Invalid path'}), 400
    
    try:
        # Expect mode as octal string like "755" or integer
        if isinstance(mode, str):
            if not re.fullmatch(r'[0-7]{3,4}', mode):
                return jsonify({'error': 'Invalid mode format'}), 400
            mode_str = mode
        elif isinstance(mode, int):
            mode_str = format(mode, 'o')
        else:
            return jsonify({'error': 'Invalid mode type'}), 400
        
        qpath = shlex.quote(path)
        qmode = shlex.quote(mode_str)
        cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"chmod {qmode} {qpath} || echo __ERR__"]
        res = subprocess.run(cmd, capture_output=True, text=True)
        if res.returncode != 0 or '__ERR__' in (res.stdout or ''):
            return jsonify({'error': (res.stderr or res.stdout or 'chmod failed').strip()}), 400
        logger.info(f'Permissions changed in container: {path} to {mode_str}')
        return jsonify({'message': 'Permissions changed successfully'})
    except Exception as e:
         return jsonify({'error': f'Could not change permissions: {str(e)}'}), 400

@app.route('/api/files/chown', methods=['POST'])
@login_required
def chown_file():
    logger.info('File chown attempt')
    path = request.json.get('path')
    owner = request.json.get('owner')
    group = request.json.get('group')
    if not path:
        return jsonify({'error': 'Path must be specified'}), 400
    
    path = os.path.normpath(path)
    if path.startswith('..') or not path.startswith('/'):
        return jsonify({'error': 'Invalid path'}), 400
    
    try:
        # Build chown target string (owner[:group])
        target = ''
        if owner and group:
            target = f"{owner}:{group}"
        elif owner and not group:
            target = str(owner)
        elif group and not owner:
            target = f":{group}"
        else:
            return jsonify({'error': 'Owner or group must be specified'}), 400
        
        qpath = shlex.quote(path)
        qtarget = shlex.quote(target)
        cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"chown {qtarget} {qpath} || echo __ERR__"]
        res = subprocess.run(cmd, capture_output=True, text=True)
        if res.returncode != 0 or '__ERR__' in (res.stdout or ''):
            return jsonify({'error': (res.stderr or res.stdout or 'chown failed').strip()}), 400
        logger.info(f'Ownership changed in container: {path} to {target}')
        return jsonify({'message': 'Ownership changed successfully'})
    except Exception as e:
        return jsonify({'error': f'Could not change ownership: {str(e)}'}), 400

@app.route('/api/files/rename', methods=['POST'])
@login_required
def rename_file():
    logger.info('File rename attempt')
    old_path = request.json.get('old_path')
    new_path = request.json.get('new_path')
    if not old_path or not new_path:
        return jsonify({'error': 'Both old and new paths must be specified'}), 400
    
    old_path = os.path.normpath(old_path)
    new_path = os.path.normpath(new_path)
    if old_path.startswith('..') or new_path.startswith('..') or not old_path.startswith('/') or not new_path.startswith('/'):
        return jsonify({'error': 'Invalid path'}), 400
    
    try:
        qold = shlex.quote(old_path)
        qnew = shlex.quote(new_path)
        cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"if [ ! -e {qold} ]; then echo __SRC_NOT_FOUND__; exit 1; fi; if [ -e {qnew} ]; then echo __DST_EXISTS__; exit 1; fi; mv {qold} {qnew} || echo __ERR__"]
        res = subprocess.run(cmd, capture_output=True, text=True)
        out = (res.stdout or '')
        if '__SRC_NOT_FOUND__' in out:
            return jsonify({'error': 'Source file or directory not found'}), 404
        if '__DST_EXISTS__' in out:
            return jsonify({'error': 'Destination already exists'}), 400
        if res.returncode != 0 or '__ERR__' in out:
            return jsonify({'error': (res.stderr or res.stdout or 'rename failed').strip()}), 400
        logger.info(f'File/directory renamed in container from {old_path} to {new_path}')
        return jsonify({'message': 'File/directory renamed successfully'})
    except Exception as e:
        return jsonify({'error': f'Could not rename: {str(e)}'}), 400

@app.route('/api/files/download', methods=['GET'])
@login_required
def download_file():
    logger.info('File download attempt')
    path = request.args.get('path')
    if not path:
        return jsonify({'error': 'No path specified'}), 400
    
    path = os.path.normpath(path)
    if path.startswith('..') or not path.startswith('/'):
        return jsonify({'error': 'Invalid path'}), 400
    
    try:
        q = shlex.quote(path)
        # Ensure it is a regular file inside container
        check_cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"[ -f {q} ] || echo __NOT_FILE__"]
        chk = subprocess.run(check_cmd, capture_output=True, text=True)
        if '__NOT_FILE__' in (chk.stdout or ''):
            return jsonify({'error': 'File not found or is a directory'}), 404
        if chk.returncode != 0:
            return jsonify({'error': (chk.stderr or chk.stdout or 'Check failed').strip()}), 400
        
        # Stream file content via docker exec cat
        def generate():
            cat_cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"cat {q}"]
            proc = subprocess.Popen(cat_cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
            try:
                while True:
                    chunk = proc.stdout.read(8192)
                    if not chunk:
                        break
                    yield chunk
            finally:
                proc.stdout.close()
                proc.wait()
        
        basename = os.path.basename(path)
        headers = {
            'Content-Type': 'application/octet-stream',
            'Content-Disposition': f'attachment; filename="{basename}"'
        }
        logger.info(f'File downloaded from container: {path}')
        return Response(generate(), headers=headers)
    except Exception as e:
        return jsonify({'error': f'Could not download file: {str(e)}'}), 400

@app.route('/api/files/compress', methods=['POST'])
@login_required
def compress_files():
    logger.info('File compression attempt')
    data = request.json
    paths = data.get('paths', [])
    archive_name = data.get('archive_name', 'archive.zip')
    
    if not paths:
        return jsonify({'error': 'No files specified'}), 400
    
    # Validate paths inside container
    safe_paths = []
    for p in paths:
        p = os.path.normpath(p)
        if p.startswith('..') or not p.startswith('/'):
            return jsonify({'error': 'Invalid path'}), 400
        safe_paths.append(p)
    
    try:
        # Determine archive directory (use dirname of first path)
        first_path = os.path.normpath(safe_paths[0])
        archive_dir = os.path.dirname(first_path) or '/'
        
        # Ensure archive name ends with .zip
        if not archive_name.endswith('.zip'):
            archive_name += '.zip'
        
        # Build zip command inside container
        qdir = shlex.quote(archive_dir)
        qarchive = shlex.quote(os.path.join(archive_dir, archive_name))
        qfiles = ' '.join(shlex.quote(p) for p in safe_paths)
        cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"cd {qdir} && zip -r {qarchive} {qfiles} || echo __ERR__"]
        res = subprocess.run(cmd, capture_output=True, text=True)
        if res.returncode != 0 or '__ERR__' in (res.stdout or ''):
            return jsonify({'error': (res.stderr or res.stdout or 'zip failed').strip()}), 400
        
        logger.info(f'Files compressed in container to: {os.path.join(archive_dir, archive_name)}')
        return jsonify({'message': 'Files compressed successfully', 'archive_path': os.path.join(archive_dir, archive_name)})
    except Exception as e:
        return jsonify({'error': f'Could not compress files: {str(e)}'}), 400

@app.route('/api/files/uncompress', methods=['POST'])
@login_required
def uncompress_file():
    logger.info('File uncompression attempt')
    data = request.json
    archive_path = data.get('path')
    extract_to = data.get('extract_to')
    
    if not archive_path:
        return jsonify({'error': 'No archive path specified'}), 400
    
    archive_path = os.path.normpath(archive_path)
    if archive_path.startswith('..') or not archive_path.startswith('/'):
        return jsonify({'error': 'Invalid path'}), 400
    
    # If extract_to is not specified, extract to the same directory as the archive
    if not extract_to:
        extract_to = os.path.dirname(archive_path)
    else:
        extract_to = os.path.normpath(extract_to)
        if extract_to.startswith('..') or not extract_to.startswith('/'):
            return jsonify({'error': 'Invalid extraction path'}), 400
    
    try:
        qarchive = shlex.quote(archive_path)
        qextract = shlex.quote(extract_to)
        # Determine archive type and extract via container tools
        if archive_path.endswith('.zip'):
            cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"unzip -o {qarchive} -d {qextract} || echo __ERR__"]
        elif archive_path.endswith(('.tar', '.tar.gz', '.tgz', '.tar.bz2')):
            cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"mkdir -p {qextract} && tar -xf {qarchive} -C {qextract} || echo __ERR__"]
        else:
            return jsonify({'error': 'Unsupported archive format. Supported: .zip, .tar, .tar.gz, .tgz, .tar.bz2'}), 400
        
        res = subprocess.run(cmd, capture_output=True, text=True)
        if res.returncode != 0 or '__ERR__' in (res.stdout or ''):
            return jsonify({'error': (res.stderr or res.stdout or 'extract failed').strip()}), 400
        
        logger.info(f'Archive extracted in container: {archive_path} to {extract_to}')
        return jsonify({'message': 'Archive extracted successfully', 'extract_path': extract_to})
    except Exception as e:
        return jsonify({'error': f'Could not extract archive: {str(e)}'}), 400

@app.route('/api/files/create-directory', methods=['POST'])
@login_required
def create_directory():
    logger.info('Directory creation attempt')
    path = request.json.get('path')
    if not path:
        return jsonify({'error': 'No path specified'}), 400
    
    path = os.path.normpath(path)
    if path.startswith('..') or not path.startswith('/'):
        return jsonify({'error': 'Invalid path'}), 400
    
    try:
        q = shlex.quote(path)
        cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"mkdir -p {q} || echo __ERR__"]
        res = subprocess.run(cmd, capture_output=True, text=True)
        if res.returncode != 0 or '__ERR__' in (res.stdout or ''):
            return jsonify({'error': (res.stderr or res.stdout or 'mkdir failed').strip()}), 400
        logger.info(f'Directory created successfully in container: {path}')
        return jsonify({'message': 'Directory created successfully'})
    except Exception as e:
        return jsonify({'error': f'Could not create directory: {str(e)}'}), 400

@app.route('/api/files/read', methods=['POST'])
@login_required
def read_file():
    path = request.json.get('path')
    if not path:
        return jsonify({'error': 'No path specified'}), 400
    
    path = os.path.normpath(path)
    if path.startswith('..') or not path.startswith('/'):
        return jsonify({'error': 'Invalid path'}), 400
    
    try:
        q = shlex.quote(path)
        # Ensure file and not directory
        check_cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"[ -f {q} ] || echo __NOT_FILE__"]
        chk = subprocess.run(check_cmd, capture_output=True, text=True)
        if '__NOT_FILE__' in (chk.stdout or ''):
            return jsonify({'error': 'File not found or is a directory'}), 404
        if chk.returncode != 0:
            return jsonify({'error': (chk.stderr or chk.stdout or 'Check failed').strip()}), 400
        
        # Read content via docker exec cat
        cat_cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"cat {q}"]
        res = subprocess.run(cat_cmd, capture_output=True, text=True)
        if res.returncode != 0:
            return jsonify({'error': (res.stderr or res.stdout or 'Read failed').strip()}), 400
        content = res.stdout
        logger.info(f'File read from container: {path}, content length: {len(content)}')
        return jsonify({
            'content': content,
            'path': path
        })
    except Exception as e:
        logger.error(f'Error reading file {path}: {str(e)}')
        return jsonify({'error': f'Could not read file: {str(e)}'}), 400

@app.route('/api/files/new', methods=['POST'])
@login_required
def create_new_file():
    try:
        data = request.json
        file_path = data.get('path')
        file_name = data.get('name')
        
        if not file_path or not file_name:
            return jsonify({'error': 'Path and filename are required'}), 400
            
        # Normalize path to prevent directory traversal
        full_path = os.path.normpath(os.path.join(file_path, file_name))
        if full_path.startswith('..') or not full_path.startswith('/'):
            return jsonify({'error': 'Invalid path'}), 400
            
        # Create empty file inside container if not exists
        qpath = shlex.quote(full_path)
        cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"[ -e {qpath} ] && echo __EXISTS__ || (mkdir -p $(dirname {qpath}) && touch {qpath}) || echo __ERR__"]
        res = subprocess.run(cmd, capture_output=True, text=True)
        out = (res.stdout or '')
        if '__EXISTS__' in out:
            return jsonify({'error': 'File already exists'}), 400
        if res.returncode != 0 or '__ERR__' in out:
            return jsonify({'error': (res.stderr or res.stdout or 'create failed').strip()}), 400
            
        logger.info(f'New file created in container: {full_path}')
        return jsonify({'message': 'File created successfully'})
    except Exception as e:
        logger.error(f'Error creating new file: {str(e)}')
        return jsonify({'error': str(e)}), 500

@app.route('/api/files/save', methods=['POST'])
@login_required
def save_file():
    path = request.json.get('path')
    content = request.json.get('content')
    if not path or content is None:
        return jsonify({'error': 'Both path and content must be specified'}), 400
    
    path = os.path.normpath(path)
    if path.startswith('..') or not path.startswith('/'):
        return jsonify({'error': 'Invalid path'}), 400
    
    try:
        qpath = shlex.quote(path)
        # Ensure file exists and is not a directory
        check_cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"[ -f {qpath} ] || echo __NOT_FILE__"]
        chk = subprocess.run(check_cmd, capture_output=True, text=True)
        if '__NOT_FILE__' in (chk.stdout or ''):
            return jsonify({'error': 'File not found or is a directory'}), 404
        if chk.returncode != 0:
            return jsonify({'error': (chk.stderr or chk.stdout or 'Check failed').strip()}), 400
        
        # Write content using docker exec and tee (safe for most text)
        # We will base64-encode content to avoid shell quoting issues
        import base64
        b64 = base64.b64encode(content.encode('utf-8')).decode('ascii')
        qb64 = shlex.quote(b64)
        cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"echo {qb64} | base64 -d > {qpath} || echo __ERR__"]
        res = subprocess.run(cmd, capture_output=True, text=True)
        if res.returncode != 0 or '__ERR__' in (res.stdout or ''):
            return jsonify({'error': (res.stderr or res.stdout or 'write failed').strip()}), 400
        return jsonify({'message': 'File saved successfully'})
    except Exception as e:
        return jsonify({'error': f'Could not save file: {str(e)}'}), 400

@app.route('/api/container/start-log/<container_name>')
@login_required
def api_container_start_log(container_name):
    """API endpoint to stream start logs for containers (PHP/MySQL) via compose."""
    # Resolve compose service name from container name
    service_name, err = resolve_service_from_container_name(container_name)
    if not service_name:
        return Response(f"data: Invalid container: {err or 'unknown'}\n\n", mimetype='text/event-stream')
    if not re.match(r'^[a-zA-Z0-9_-]+$', service_name):
        return Response("data: Invalid service name\n\n", mimetype='text/event-stream')
    if service_name == 'panel':
        return Response("data: Cannot control panel container\n\n", mimetype='text/event-stream')

    project = get_compose_project()
    base = get_compose_cmd_base()
    resolved_file = get_resolved_compose_file()
    cmd = base + ['-f', resolved_file] + (['--project-name', project] if project else []) + ['up', '-d', '--no-deps', service_name]

    log_dir = '/app/tmp'
    try:
        os.makedirs(log_dir, exist_ok=True)
    except Exception:
        pass
    log_path = os.path.join(log_dir, f"start_{service_name}_{int(time.time())}.log")

    def generate():
        yield f"data: Running: {' '.join(cmd)}\n\n"
        try:
            with open(log_path, 'w') as lf:
                lf.write(f"Running: {' '.join(cmd)}\n")
                env = os.environ.copy()
                proj_dir = get_project_dir()
                if proj_dir:
                    env['HOST_PROJECT_DIR'] = proj_dir
                proc = subprocess.Popen(
                    cmd,
                    cwd='/workspace/docker',
                    stdout=subprocess.PIPE,
                    stderr=subprocess.STDOUT,
                    text=True,
                    bufsize=1
                )
                for line in proc.stdout:
                    if line:
                        lf.write(line)
                        lf.flush()
                        yield f"data: {line.strip()}\n\n"
                rc = proc.wait()
                lf.write(f"\nStart finished with code {rc}\n")
                yield "event: done\n"
                yield f"data: {'success' if rc == 0 else 'error'}\n\n"
        except Exception as e:
            try:
                with open(log_path, 'a') as lf:
                    lf.write(f"\nStart error: {str(e)}\n")
            except Exception:
                pass
            yield f"data: Start error: {str(e)}\n\n"
            yield "event: done\n"
    
    return Response(generate(), mimetype='text/event-stream')

# Helper to resolve compose service name from a container name
def resolve_service_from_container_name(container_name):
    try:
        res = subprocess.run(
            ['docker', 'inspect', '-f', '{{ index .Config.Labels "com.docker.compose.service" }}|{{ index .Config.Labels "com.docker.compose.project" }}', container_name],
            capture_output=True, text=True, timeout=5
        )
        if res.returncode != 0:
            return None, "Container not found"
        out = res.stdout.strip()
        parts = out.split('|') if out else []
        service = parts[0] if len(parts) > 0 else ''
        project = parts[1] if len(parts) > 1 else ''
        expected_project = get_compose_project()
        if expected_project and project and project != expected_project:
            return None, "Container belongs to different project"
        if not service:
            if container_name.startswith('mlite_'):
                return container_name.replace('mlite_', ''), None
            return None, "Service label missing"
        return service, None
    except Exception as e:
        return None, str(e)

@app.route('/api/container/<container_name>/<action>', methods=['POST'])
@login_required
def api_container_control(container_name, action):
    """API endpoint to control containers (PHP/MySQL)"""
    if action not in ['start', 'stop', 'restart']:
        return jsonify({
            'success': False,
            'error': 'Invalid action'
        }), 400

    service_name, err = resolve_service_from_container_name(container_name)
    if not service_name:
        return jsonify({
            'success': False,
            'error': f'Invalid container: {err or "unknown"}'
        }), 400

    if service_name == 'panel':
        return jsonify({
            'success': False,
            'error': 'Cannot control panel container'
        }), 400

    if action == 'start':
        success, message = start_container(service_name)
    elif action == 'stop':
        success, message = stop_container(service_name)
    else:
        success, message = restart_container(service_name)

    if success:
        return jsonify({
            'success': True,
            'message': message
        })
    else:
        return jsonify({
            'success': False,
            'error': message
        }), 500

if __name__ == '__main__':
    # Ensure nginx conf directory exists
    os.makedirs(NGINX_CONF_DIR, exist_ok=True)
    
    port = int(os.environ.get('PORT') or os.environ.get('FLASK_RUN_PORT') or 5000)
    app.run(host='0.0.0.0', port=port, debug=True)
