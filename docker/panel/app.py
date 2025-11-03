import os
import subprocess
import requests
import shlex
import re
import shutil
import yaml
import json
from datetime import datetime, timezone, timedelta
from flask import Flask, render_template, request, redirect, url_for, flash, jsonify, Response, session
from flask import stream_with_context
import logging
import time
from functools import wraps
from collections import deque

logging.basicConfig(level=logging.INFO, format='[%(asctime)s] %(levelname)s in %(module)s: %(message)s')
logger = logging.getLogger(__name__)

# MySQL (Docker-based) helpers
# Use docker exec to run mysql commands inside the mlite_mysql container.
# Provide minimal compatibility with cursor/connection interface used by routes.
try:
    import mysql.connector  # optional, only for Error types referenced in except blocks
except Exception:
    class _DummyMySQLError(Exception):
        pass
    class _DummyPoolError(Exception):
        pass
    class _DummyPooling:
        class MySQLConnectionPool:
            def __init__(self, **kwargs):
                pass
            def _remove_connections(self):
                pass
    class _DummyConnector:
        Error = _DummyMySQLError
        PoolError = _DummyPoolError
        pooling = _DummyPooling
    class mysql:
        connector = _DummyConnector()

MYSQL_CONTAINER_NAME = os.environ.get('MYSQL_CONTAINER_NAME', 'mlite_mysql')

def get_mysql_config_path():
    """Return path to persisted MySQL config JSON for root access."""
    try:
        base_dir = os.path.dirname(os.path.abspath(__file__))
        return os.path.join(base_dir, 'mysql_config.json')
    except Exception:
        return 'mysql_config.json'


def load_mysql_config():
    """Load MySQL access config from JSON (root user/password)."""
    default_cfg = {
        'user': 'root',
        'password': '',
        'host': 'localhost',  # within container
    }
    cfg_path = get_mysql_config_path()
    try:
        if os.path.exists(cfg_path):
            with open(cfg_path, 'r') as f:
                data = json.load(f)
                if isinstance(data, dict):
                    # ensure keys
                    for k in default_cfg:
                        data.setdefault(k, default_cfg[k])
                    return data
    except Exception as e:
        logger.warning(f"Failed to load MySQL config: {e}")
    return default_cfg


def save_mysql_config(cfg: dict) -> bool:
    """Persist MySQL access config to JSON file."""
    try:
        cfg_path = get_mysql_config_path()
        with open(cfg_path, 'w') as f:
            json.dump(cfg or {}, f)
        return True
    except Exception as e:
        logger.error(f"Failed to save MySQL config: {e}")
        return False

# Global configuration used by routes
_db_cfg_loaded = load_mysql_config()
# Expose as the name expected by routes
db_config = {
    'user': _db_cfg_loaded.get('user', 'root'),
    'password': _db_cfg_loaded.get('password', ''),
    # The following keys are kept for compatibility when routes pass db_config to pooling
    'host': _db_cfg_loaded.get('host', 'localhost'),
    'database': None,
}
connection_pool = None  # not used with docker-based access, kept for compatibility


def _build_mysql_cli_args(user: str, password: str, database: str = None, batch: bool = True, skip_column_names: bool = False):
    args = ['mysql', f'-u{user}']
    if password:
        args.append(f'-p{password}')
    if database:
        args.extend(['-D', database])
    if batch:
        args.append('--batch')
        args.append('--raw')
    if skip_column_names:
        args.append('--skip-column-names')
    return args


def _exec_mysql(sql: str, db: str = None, expect_output: bool = True, dictionary: bool = False):
    """Run given SQL inside MySQL container and parse output.
    - When dictionary=True, keep column names in first line and return list of dicts.
    - When dictionary=False, skip column names and return list of lists.
    """
    user = db_config.get('user', 'root')
    pwd = db_config.get('password', '')
    skip_cols = not dictionary
    cli_args = _build_mysql_cli_args(user, pwd, database=db, batch=True, skip_column_names=skip_cols)
    cmd = ['docker', 'exec', MYSQL_CONTAINER_NAME] + cli_args + ['-e', sql]
    try:
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=30)
        if result.returncode != 0:
            stderr = (result.stderr or '').strip()
            stdout = (result.stdout or '').strip()
            raise RuntimeError(stderr or stdout or 'mysql cli error')
        out = (result.stdout or '').strip()
        if not expect_output:
            return []
        if not out:
            return []
        lines = out.splitlines()
        # Parse tab-separated output
        if dictionary:
            if not lines:
                return []
            headers = lines[0].split('\t')
            rows = []
            for line in lines[1:]:
                cols = line.split('\t')
                row = {headers[i]: (cols[i] if i < len(cols) else None) for i in range(len(headers))}
                rows.append(row)
            return rows
        else:
            rows = []
            for line in lines:
                cols = line.split('\t')
                rows.append(cols)
            return rows
    except Exception as e:
        logger.error(f"MySQL exec error: {e}")
        raise


def _format_sql_with_params(sql: str, params: tuple or list or None):
    if not params:
        return sql
    def esc(val):
        s = str(val)
        s = s.replace("\\", "\\\\").replace("'", "\\'")
        return f"'{s}'"
    out = sql
    for p in params:
        out = out.replace('%s', esc(p), 1)
    return out


class DockerMySQLCursor:
    def __init__(self, dictionary: bool = False, database: str or None = None):
        self.dictionary = dictionary
        self.database = database
        self._last_result = []
        self.rowcount = 0
    def execute(self, sql: str, params: tuple or list or None = None):
        formatted = _format_sql_with_params(sql, params)
        # Decide if we expect output: SELECT/SHOW/DESCRIBE returns rows
        is_select_like = bool(re.match(r"\s*(SELECT|SHOW|DESCRIBE|EXPLAIN)\b", formatted, re.IGNORECASE))
        if is_select_like:
            self._last_result = _exec_mysql(formatted, db=self.database, expect_output=True, dictionary=self.dictionary)
            # For select-like, rowcount equals number of returned rows
            try:
                self.rowcount = len(self._last_result)
            except Exception:
                self.rowcount = 0
        else:
            # Execute DML/DDL, then query ROW_COUNT() to emulate affected rows
            rows = _exec_mysql(f"{formatted}; SELECT ROW_COUNT();", db=self.database, expect_output=True, dictionary=False)
            # ROW_COUNT() result should be last line
            try:
                if rows:
                    last = rows[-1]
                    # rows are list of columns; take first value
                    val = int((last[0] if isinstance(last, (list, tuple)) and last else last))
                    self.rowcount = val
                else:
                    self.rowcount = 0
            except Exception:
                self.rowcount = 0
            # Non-select queries don't have result sets
            self._last_result = []
    def fetchall(self):
        return self._last_result
    def fetchone(self):
        try:
            return self._last_result[0] if self._last_result else None
        except Exception:
            return None
    def close(self):
        pass


class DockerMySQLConnection:
    def __init__(self, database: str or None = None):
        self.database = database
        self._autocommit = True
    def cursor(self, dictionary: bool = False):
        return DockerMySQLCursor(dictionary=dictionary, database=self.database)
    def commit(self):
        # mysql CLI executes in autocommit by default, nothing needed
        pass
    def close(self):
        pass
    # Provide minimal compatibility with connector API
    @property
    def autocommit(self):
        return self._autocommit
    @autocommit.setter
    def autocommit(self, value):
        # No effect; mysql CLI runs statements independently
        self._autocommit = bool(value)
    def cmd_query(self, sql: str):
        # Handle USE to switch default database; otherwise run as no-output command
        m = re.match(r"\s*USE\s+`?([a-zA-Z0-9_]+)`?\s*;?\s*$", sql, re.IGNORECASE)
        if m:
            self.database = m.group(1)
        try:
            _exec_mysql(sql, db=self.database, expect_output=False, dictionary=False)
        except Exception as e:
            # Surface error via exception to mimic connector behavior
            raise e


def create_mysql_connection(database: str or None = None):
    """Return a docker-backed MySQL connection shim compatible with route usage."""
    return DockerMySQLConnection(database=database)


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
    """Create directory via Nginx container (shared mount) if not exists with 0755 permissions and root ownership."""
    try:
        quoted = shlex.quote(path)
        cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"mkdir -p {quoted} && chmod 755 {quoted} && chown root:root {quoted}"]
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
            cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"cat > {quoted_index} << 'EOF'\n{content}\nEOF\nchmod 644 {quoted_index} && chown root:root {quoted_index}"]
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

    location /admin {{
        try_files $uri $uri/ /admin/index.php?$args;
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

    # Izinkan hanya untuk ACME challenge
    location ^~ /.well-known/acme-challenge/ {{
        modsecurity off;
        root /var/www/certbot;
        allow all;
        default_type "text/plain";
        try_files $uri =404;
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

    # Izinkan hanya untuk ACME challenge
    location ^~ /.well-known/acme-challenge/ {{
        modsecurity off;
        root /var/www/certbot;
        allow all;
        default_type "text/plain";
        try_files $uri =404;
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

    # Izinkan hanya untuk ACME challenge
    location ^~ /.well-known/acme-challenge/ {{
        modsecurity off;
        root /var/www/certbot;
        allow all;
        default_type "text/plain";
        try_files $uri =404;
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
    """Ensure /workspace/docker/.env contains HOST_PROJECT_DIR without overwriting other variables."""
    try:
        proj_dir = get_project_dir()
        if not proj_dir:
            return False
        env_path = '/workspace/docker/.env'

        # Read existing .env content if available
        existing_lines = []
        try:
            with open(env_path, 'r') as f:
                existing_lines = f.readlines()
        except FileNotFoundError:
            existing_lines = []

        # Prepare the updated HOST_PROJECT_DIR line
        host_line = f"HOST_PROJECT_DIR={proj_dir}\n"
        updated = False
        for idx, line in enumerate(existing_lines):
            stripped = line.strip()
            if stripped.startswith('HOST_PROJECT_DIR='):
                existing_lines[idx] = host_line
                updated = True
                break

        # Append if not present
        if not updated:
            existing_lines.append(host_line)

        # Write back, preserving other variables
        with open(env_path, 'w') as f:
            f.writelines(existing_lines)
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


def remove_container(service_name):
    """Remove (delete) container(s) for a specific service"""
    if service_name == 'panel':
        logger.warning("Attempted to remove panel container; operation blocked")
        return False, "Cannot control panel container"
    try:
        project = get_compose_project()
        base = get_compose_cmd_base()
        resolved_file = get_resolved_compose_file()
        cmd = base + ['-f', resolved_file] + (['--project-name', project] if project else []) + ['rm', '-f', '-s', service_name]
        logger.info(f"Removing container: service={service_name}, cmd={' '.join(cmd)}")
        start_time = datetime.now()
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=60)
        duration = (datetime.now() - start_time).total_seconds()
        logger.info(f"Remove result: rc={result.returncode}, duration={duration}s, stdout='{result.stdout.strip()}', stderr='{result.stderr.strip()}'")
        
        if result.returncode == 0:
            return True, f"Container {service_name} removed successfully"
        # Fallback: remove by container IDs filtered by labels
        logger.info(f"Compose rm failed for service={service_name}, attempting docker rm by labels")
        label_filters = ['--filter', f'label=com.docker.compose.service={service_name}']
        if project:
            label_filters += ['--filter', f'label=com.docker.compose.project={project}']
        ps_cmd = ['docker', 'ps', '-a', '-q'] + label_filters
        ps_res = subprocess.run(ps_cmd, capture_output=True, text=True, timeout=10)
        ids = [line.strip() for line in ps_res.stdout.splitlines() if line.strip()]
        logger.info(f"Fallback discovered {len(ids)} container(s) for service={service_name}: {ids}")
        if ids:
            rm_cmd = ['docker', 'rm', '-f'] + ids
            rm_res = subprocess.run(rm_cmd, capture_output=True, text=True, timeout=30)
            logger.info(f"Fallback rm result: rc={rm_res.returncode}, stdout='{rm_res.stdout.strip()}', stderr='{rm_res.stderr.strip()}'")
            if rm_res.returncode == 0:
                return True, f"Container(s) for {service_name} removed"
            else:
                return False, f"Fallback remove failed: {rm_res.stderr or rm_res.stdout}"
        else:
            return False, f"Remove failed: {result.stderr or result.stdout}"
    except subprocess.TimeoutExpired as te:
        logger.error(f"Remove timeout for service={service_name}: {str(te)}")
        return False, "Remove timeout (1 minute)"
    except Exception as e:
        logger.exception(f"Remove error for service={service_name}")
        return False, f"Remove error: {str(e)}"


def remove_image(service_name):
    """Remove built image(s) for a specific service (build-capable)."""
    if service_name == 'panel':
        logger.warning("Attempted to remove panel image; operation blocked")
        return False, "Cannot control panel container"
    try:
        project = get_compose_project()
        images_to_remove = set()
        # Prefer discovering images via existing containers
        label_filters = ['--filter', f'label=com.docker.compose.service={service_name}']
        if project:
            label_filters += ['--filter', f'label=com.docker.compose.project={project}']
        ps_cmd = ['docker', 'ps', '-a', '-q'] + label_filters
        ps_res = subprocess.run(ps_cmd, capture_output=True, text=True, timeout=10)
        ids = [line.strip() for line in ps_res.stdout.splitlines() if line.strip()]
        for cid in ids:
            insp = subprocess.run(['docker', 'inspect', '-f', '{{.Image}}|{{.Config.Image}}', cid], capture_output=True, text=True, timeout=5)
            if insp.returncode == 0:
                out = (insp.stdout or '').strip()
                parts = out.split('|') if out else []
                img_id = parts[0] if len(parts) > 0 else ''
                if img_id:
                    images_to_remove.add(img_id)
        # If no containers found, try image repository patterns
        if not images_to_remove:
            patterns = []
            if project:
                patterns.append(f"{project}-{service_name}")
                patterns.append(f"{project}_{service_name}")
            patterns.append(f"mlite_{service_name}")
            img_res = subprocess.run(['docker', 'images', '--format', '{{.Repository}} {{.ID}}'], capture_output=True, text=True, timeout=10)
            for line in (img_res.stdout or '').splitlines():
                try:
                    repo, imgid = line.strip().split()
                except ValueError:
                    continue
                if any(p in repo for p in patterns):
                    images_to_remove.add(imgid)
        if not images_to_remove:
            return False, "No images found for service"
        rm_cmd = ['docker', 'rmi', '-f'] + list(images_to_remove)
        rm_res = subprocess.run(rm_cmd, capture_output=True, text=True, timeout=60)
        if rm_res.returncode == 0:
            return True, f"Image(s) for {service_name} removed"
        else:
            return False, f"Remove image failed: {rm_res.stderr or rm_res.stdout}"
    except subprocess.TimeoutExpired as te:
        logger.error(f"Remove image timeout for service={service_name}: {str(te)}")
        return False, "Remove image timeout (1 minute)"
    except Exception as e:
        logger.exception(f"Remove image error for service={service_name}")
        return False, f"Remove image error: {str(e)}"

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
            # For API endpoints, return JSON instead of HTML login redirect
            try:
                is_api = request.path.startswith('/api/')
            except Exception:
                is_api = False
            if is_api:
                return jsonify({'success': False, 'error': 'Unauthorized'}), 401
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
    
    # MySQL status and total databases
    mysql_containers = get_mysql_containers()
    mysql_running = any(c.get('running') for c in mysql_containers) if mysql_containers else False
    total_databases = 0
    if mysql_running:
        try:
            rows = _exec_mysql('SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name NOT IN ("mysql","information_schema","performance_schema","sys")', expect_output=True, dictionary=False)
            if rows and rows[0] and rows[0][0].isdigit():
                total_databases = int(rows[0][0])
            else:
                # Fallback: SHOW DATABASES and count non-system schemas
                dbs = _exec_mysql('SHOW DATABASES', expect_output=True, dictionary=False)
                if dbs:
                    non_system = [r[0] for r in dbs if r and r[0] not in ['mysql','information_schema','performance_schema','sys']]
                    total_databases = len(non_system)
        except Exception:
            total_databases = 0
    
    return render_template('dashboard.html', sites=sites, nginx_status=nginx_status, mysql_running=mysql_running, total_databases=total_databases)

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

@app.route('/api/vhost/<site_name>', methods=['GET'])
@login_required
def api_vhost(site_name):
    if not is_valid_site_name(site_name):
        return jsonify({'error': 'Invalid site name'}), 400
    ok, content, err = read_conf_file(site_name)
    if not ok:
        return jsonify({'error': err}), 404
    return jsonify({'site_name': site_name, 'content': content})

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
    # Build local Docker images catalog once
    images_catalog = {}
    project_name = None
    try:
        project_name = get_compose_project()
    except Exception:
        project_name = None
    try:
        img_res = subprocess.run(['docker', 'images', '--format', '{{.Repository}}|{{.Tag}}|{{.ID}}'], capture_output=True, text=True, timeout=10)
        for line in (img_res.stdout or '').splitlines():
            parts = line.strip().split('|')
            if len(parts) >= 3:
                repo = parts[0] or ''
                tag = parts[1] or ''
                img_id = parts[2] or ''
                full = f"{repo}:{tag}" if repo and tag else repo
                if full:
                    images_catalog[full] = img_id
    except Exception as e:
        print(f"[WARN] Failed to list docker images: {e}")

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
        # Determine if an image exists for this service
        container_exists = container_name in docker_ps_status
        has_image = False
        if container_exists:
            has_image = True
        elif service.get('build'):
            patterns = []
            if project_name:
                patterns.append(f"{project_name}-{service_name}")
                patterns.append(f"{project_name}_{service_name}")
            patterns.append(f"mlite_{service_name}")
            for repo in images_catalog.keys():
                if any(p in repo for p in patterns):
                    has_image = True
                    break
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
            'depends_on': service['depends_on'],
            'exists': container_exists,
            'has_container': container_exists,
            'has_image': has_image
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

@app.route('/container/remove/<service_name>', methods=['POST'])
@login_required
def container_remove(service_name):
    """Remove specific container"""
    logger.info(f"[container_remove] POST received for service='{service_name}'")
    if not re.match(r'^[a-zA-Z0-9_-]+$', service_name):
        logger.warning(f"[container_remove] Invalid service name: '{service_name}'")
        flash('Invalid service name', 'error')
        return redirect(url_for('containers'))
    
    success, message = remove_container(service_name)
    logger.info(f"[container_remove] remove_container returned success={success}, message='{message}'")
    if success:
        flash(message, 'success')
    else:
        if 'Cannot control panel container' in message:
            flash('Tidak dapat menghapus container panel dari UI.', 'error')
        else:
            flash(f"Gagal hapus container: {message}.", 'error')
    
    return redirect(url_for('containers'))

@app.route('/container/remove-image/<service_name>', methods=['POST'])
@login_required
def container_remove_image(service_name):
    """Remove image for specific service"""
    logger.info(f"[container_remove_image] POST received for service='{service_name}'")
    if not re.match(r'^[a-zA-Z0-9_-]+$', service_name):
        logger.warning(f"[container_remove_image] Invalid service name: '{service_name}'")
        flash('Invalid service name', 'error')
        return redirect(url_for('containers'))
    
    success, message = remove_image(service_name)
    logger.info(f"[container_remove_image] remove_image returned success={success}, message='{message}'")
    if success:
        flash(message, 'success')
    else:
        if 'Cannot control panel container' in message:
            flash('Tidak dapat menghapus image panel dari UI.', 'error')
        else:
            flash(f"Gagal hapus image: {message}.", 'error')
    
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
        try:
            logger.info(f"[nginx_page] NGINX_CONF_DIR={NGINX_CONF_DIR}")
        except Exception:
            pass
        if os.path.exists(NGINX_CONF_DIR):
            try:
                dir_list = sorted(os.listdir(NGINX_CONF_DIR))
                logger.info(f"[nginx_page] conf.d list: {dir_list}")
                siti_path = os.path.join(NGINX_CONF_DIR, 'siti.loc.conf')
                logger.info(f"[nginx_page] siti.loc.conf path: {siti_path} exists={os.path.exists(siti_path)}")
            except Exception as e:
                logger.warning(f"[nginx_page] Failed to list conf dir: {e}")
            for filename in os.listdir(NGINX_CONF_DIR):
                if filename.endswith('.conf') or filename.endswith('.conf.disabled'):
                    filepath = os.path.join(NGINX_CONF_DIR, filename)
                    try:
                        with open(filepath, 'r') as f:
                            content = f.read()
                        # Parse config info
                        enabled = filename.endswith('.conf')
                        domain = filename.replace('.conf.disabled', '').replace('.conf', '')
                        try:
                            logger.info(f"[nginx_page] Found config file {filename}, domain={domain}, enabled={enabled}")
                        except Exception:
                            pass
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
                        # SSL status parsing
                        ssl_enabled = ('listen 443' in content) and ('ssl_certificate' in content)
                        https_redirect = ('return 301 https://$host$request_uri' in content) or ('return 301 https://' in content)
                        config_files.append({
                            'domain': domain,
                            'type': site_type,
                            'port': port,
                            'root_dir': root_dir,
                            'filename': filename,
                            'created': datetime.fromtimestamp(os.path.getctime(filepath)).strftime('%Y-%m-%d %H:%M:%S'),
                            'enabled': enabled,
                            'ssl_enabled': ssl_enabled,
                            'https_redirect': https_redirect
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

# --- SSL Helpers & API ---

def _ssl_paths(domain: str):
    ssl_dir = os.path.join(NGINX_CONF_DIR, 'ssl', domain)
    return ssl_dir, os.path.join(ssl_dir, 'fullchain.pem'), os.path.join(ssl_dir, 'privkey.pem')

@app.route('/api/nginx/ssl/status/<domain>', methods=['GET'])
@login_required
def api_ssl_status(domain):
    try:
        if not is_valid_site_name(domain):
            return jsonify({'success': False, 'error': 'Invalid domain'}), 400
        conf_path = get_conf_path(domain)
        disabled_path = os.path.normpath(os.path.join(NGINX_CONF_DIR, f"{domain}.conf.disabled"))
        target_path = conf_path if os.path.exists(conf_path) else (disabled_path if os.path.exists(disabled_path) else None)
        if not target_path:
            return jsonify({'success': False, 'error': 'Config not found'}), 404
        with open(target_path, 'r') as f:
            content = f.read()
        ssl_enabled = ('listen 443' in content) and ('ssl_certificate' in content)
        https_redirect = ('return 301 https://$host$request_uri' in content) or ('return 301 https://' in content)
        ssl_dir, cert_path, key_path = _ssl_paths(domain)
        # Check certs inside nginx container as primary source
        container_fullchain = f"/etc/letsencrypt/live/{domain}/fullchain.pem"
        container_privkey = f"/etc/letsencrypt/live/{domain}/privkey.pem"
        container_cert_exists = False
        try:
            check_cmd = [
                'docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c',
                f"test -f {shlex.quote(container_fullchain)} && test -f {shlex.quote(container_privkey)} && echo OK || echo MISSING"
            ]
            chk = subprocess.run(check_cmd, capture_output=True, text=True, timeout=10)
            container_cert_exists = (chk.returncode == 0 and 'OK' in (chk.stdout or ''))
        except Exception:
            container_cert_exists = False
        host_cert_exists = os.path.exists(cert_path) and os.path.exists(key_path)
        return jsonify({
            'success': True,
            'ssl_enabled': ssl_enabled,
            'https_redirect': https_redirect,
            'cert_exists': container_cert_exists or host_cert_exists,
            'cert_path': f'/etc/letsencrypt/live/{domain}/fullchain.pem',
            'key_path': f'/etc/letsencrypt/live/{domain}/privkey.pem',
            'host_cert_path': f'/etc/nginx/conf.d/ssl/{domain}/fullchain.pem',
            'host_key_path': f'/etc/nginx/conf.d/ssl/{domain}/privkey.pem',
            'source': 'container' if container_cert_exists else ('host' if host_cert_exists else 'none')
        })
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

@app.route('/api/nginx/ssl/configure', methods=['POST'])
@login_required
def api_ssl_configure():
    try:
        payload = request.json if request.is_json else request.form
        domain = payload.get('domain')
        enable_ssl = str(payload.get('enable_ssl', 'true')).lower() in ['true', '1', 'yes']
        force_redirect = str(payload.get('force_redirect', 'true')).lower() in ['true', '1', 'yes']
        if not domain or not is_valid_site_name(domain):
            return jsonify({'success': False, 'error': 'Invalid domain'}), 400
        conf_path = get_conf_path(domain)
        disabled_path = os.path.normpath(os.path.join(NGINX_CONF_DIR, f"{domain}.conf.disabled"))
        target_path = conf_path if os.path.exists(conf_path) else (disabled_path if os.path.exists(disabled_path) else None)
        if not target_path:
            return jsonify({'success': False, 'error': 'Config not found'}), 404
        with open(target_path, 'r') as f:
            content = f.read()
        ssl_dir, cert_path, key_path = _ssl_paths(domain)
        # Try Let's Encrypt via certbot container (webroot) if email provided
        le_live_dir = os.path.join('/etc', 'letsencrypt', 'live', domain)
        if (not os.path.exists(cert_path) or not os.path.exists(key_path)) and (payload.get('email') or os.environ.get('LE_EMAIL')):
            try:
                email_addr = (payload.get('email') or os.environ.get('LE_EMAIL'))
                # Ensure ACME webroot exists inside Nginx container
                try:
                    subprocess.run(['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', 'mkdir -p /var/www/certbot/.well-known/acme-challenge'], capture_output=True, text=True, timeout=20)
                except Exception:
                    pass
                # Run certbot inside Nginx container using webroot challenge
                cmd = [
                    'docker', 'exec', NGINX_CONTAINER_NAME, 'certbot', 'certonly',
                    '--webroot', '-w', '/var/www/certbot',
                    '-d', domain, '--email', email_addr,
                    '--agree-tos', '--non-interactive', '--preferred-challenges', 'http'
                ]
                result = subprocess.run(cmd, capture_output=True, text=True, timeout=240)
                if result.returncode == 0:
                    # Verify certs exist inside container only; no copy to host
                    src_fullchain_abs = f"/etc/letsencrypt/live/{domain}/fullchain.pem"
                    src_privkey_abs = f"/etc/letsencrypt/live/{domain}/privkey.pem"
                    try:
                        check_cmd = [
                            'docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c',
                            f"test -f {shlex.quote(src_fullchain_abs)} && test -f {shlex.quote(src_privkey_abs)} && echo OK || echo MISSING"
                        ]
                        check = subprocess.run(check_cmd, capture_output=True, text=True, timeout=20)
                        if check.returncode == 0 and 'OK' in (check.stdout or ''):
                            logger.info(f"Let's Encrypt certs present in container for {domain}")
                        else:
                            logger.warning(
                                f"Certbot succeeded but certs not found in container for {domain}: "
                                f"stdout={(check.stdout or '').strip()} stderr={(check.stderr or '').strip()}"
                            )
                    except Exception as e:
                        logger.warning(f"Failed to verify certs in container for {domain}: {e}")
                else:
                    logger.warning(f"Certbot (nginx) failed for {domain}: {result.stderr or result.stdout}")
            except Exception as ce:
                logger.error(f"Certbot (nginx) error for {domain}: {ce}")
        # Determine certificate availability from container or host
        container_fullchain = f"/etc/letsencrypt/live/{domain}/fullchain.pem"
        container_privkey = f"/etc/letsencrypt/live/{domain}/privkey.pem"
        container_cert_exists = False
        try:
            check_cmd = [
                'docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c',
                f"test -f {shlex.quote(container_fullchain)} && test -f {shlex.quote(container_privkey)} && echo OK || echo MISSING"
            ]
            chk = subprocess.run(check_cmd, capture_output=True, text=True, timeout=10)
            container_cert_exists = (chk.returncode == 0 and 'OK' in (chk.stdout or ''))
        except Exception:
            container_cert_exists = False
        host_cert_exists = os.path.exists(cert_path) and os.path.exists(key_path)
        if enable_ssl and not (container_cert_exists or host_cert_exists):
            return jsonify({'success': False, 'error': "Let's Encrypt certificate not available. Ensure port 80 is open, DNS points to host, ACME challenge is served at '/.well-known/acme-challenge', and a valid email is provided. Check panel logs for certbot output."}), 400
        ssl_enabled = ('listen 443' in content) and ('ssl_certificate' in content)
        https_redirect_present = ('return 301 https://$host$request_uri' in content) or ('return 301 https://' in content)
        if enable_ssl and not ssl_enabled:
            # Build SSL server block based on site type
            site_type = 'proxy'
            port = None
            root_dir = None
            php_fastcgi_line = None
            # Helper: extract root from main server (listen 80) block, ignoring location blocks
            def _extract_root_from_main_server(c: str):
                lines = c.splitlines()
                depth = 0
                in_server = False
                saw_listen80 = False
                root_val = None
                for line in lines:
                    stripped = line.strip()
                    if not in_server and re.search(r'\bserver\s*\{', line) and depth == 0:
                        in_server = True
                        saw_listen80 = False
                        root_val = None
                        depth += line.count('{') - line.count('}')
                        continue
                    if in_server:
                        if depth == 1 and re.search(r'\blisten\s+(?:\S+:)?80\b', stripped):
                            saw_listen80 = True
                        if depth == 1 and stripped.startswith('root '):
                            if root_val is None:
                                m = re.search(r'root\s+([^;]+);', stripped)
                                if m:
                                    root_val = m.group(1).strip()
                        depth += line.count('{') - line.count('}')
                        if depth <= 0:
                            if saw_listen80:
                                return root_val
                            in_server = False
                    else:
                        depth += line.count('{') - line.count('}')
                return None

            if 'fastcgi_pass' in content:
                site_type = 'php'
                # Use root from main server block, ignore ACME location root
                root_dir = _extract_root_from_main_server(content)
                # Capture fastcgi_pass line as-is
                for line in content.split('\n'):
                    if 'fastcgi_pass' in line:
                        php_fastcgi_line = line.strip()
                        break
            elif 'proxy_pass' in content:
                site_type = 'proxy'
                for line in content.split('\n'):
                    if 'proxy_pass http://localhost:' in line:
                        try:
                            port = line.split('proxy_pass http://localhost:')[1].split(';')[0].strip()
                        except Exception:
                            pass
                        break
            else:
                site_type = 'static'
                # Use root from main server block, ignore ACME location root
                root_dir = _extract_root_from_main_server(content)
            # Choose cert paths: prefer host copies if present, else container paths
            use_host_paths = os.path.exists(cert_path) and os.path.exists(key_path)
            cert_ref = cert_path if use_host_paths else f"/etc/letsencrypt/live/{domain}/fullchain.pem"
            key_ref = key_path if use_host_paths else f"/etc/letsencrypt/live/{domain}/privkey.pem"
            ssl_block = [
                'server {',
                '    listen 443 ssl;',
                f'    server_name {domain};',
                f'    ssl_certificate {cert_ref};',
                f'    ssl_certificate_key {key_ref};',
                '    ssl_protocols TLSv1.2 TLSv1.3;',
                '    ssl_prefer_server_ciphers on;',
            ]
            if site_type == 'php':
                if root_dir:
                    ssl_block.append(f'    root {root_dir};')
                ssl_block.extend([
                    '    index index.php index.html;',
                    '',
                    '    location / {',
                    '        try_files $uri $uri/ /index.php?$args;',
                    '    }',
                    '    location /admin {',
                    '        try_files $uri $uri/ /admin/index.php?$args;',
                    '    }',
                    '    location ~ \\.php$ {',
                    '        include fastcgi_params;',
                    php_fastcgi_line or '        fastcgi_pass php83:9000;',
                    '        fastcgi_index index.php;',
                    '        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;',
                    '        fastcgi_buffer_size 128k;',
                    '        fastcgi_buffers 4 256k;',
                    '        fastcgi_busy_buffers_size 256k;',
                    '    }',
                    '    location ~ /\\. {',
                    '        deny all;',
                    '    }',
                ])
            elif site_type == 'proxy':
                ssl_block.extend([
                    '    location / {',
                    f'        proxy_pass http://localhost:{port or "80"};',
                    '        proxy_set_header Host $host;',
                    '        proxy_set_header X-Real-IP $remote_addr;',
                    '        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;',
                    '        proxy_set_header X-Forwarded-Proto $scheme;',
                    '    }',
                ])
            else:
                if root_dir:
                    ssl_block.append(f'    root {root_dir};')
                ssl_block.extend([
                    '    index index.html;',
                    '    location / {',
                    '        try_files $uri $uri/ /index.html;',
                    '    }',
                    '    location ~ /\\ . {',
                    '        deny all;',
                    '    }',
                ])
            ssl_block.append('}')
            content = content + '\n' + '\n'.join(ssl_block) + '\n'
        if force_redirect and not https_redirect_present:
            redirect_block = (
                'server {\n'
                '    listen 80;\n'
                f'    server_name {domain};\n'
                '    location /.well-known/acme-challenge/ {\n'
                '        root /var/www/certbot;\n'
                '        try_files $uri =404;\n'
                '    }\n'
                '    location / {\n'
                '        return 301 https://$host$request_uri;\n'
                '    }\n'
                '}\n'
            )
            content = redirect_block + '\n' + content
        ok_write, err_write = write_conf_file(domain, content)
        if not ok_write:
            return jsonify({'success': False, 'error': err_write}), 500
        success, message = reload_nginx()
        return jsonify({'success': success, 'message': message})
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

@app.route('/api/nginx/ssl/disable', methods=['POST'])
@login_required
def api_ssl_disable():
    try:
        payload = request.json if request.is_json else request.form
        domain = payload.get('domain')
        if not domain or not is_valid_site_name(domain):
            return jsonify({'success': False, 'error': 'Invalid domain'}), 400
        conf_path = get_conf_path(domain)
        disabled_path = os.path.normpath(os.path.join(NGINX_CONF_DIR, f"{domain}.conf.disabled"))
        target_path = conf_path if os.path.exists(conf_path) else (disabled_path if os.path.exists(disabled_path) else None)
        if not target_path:
            return jsonify({'success': False, 'error': 'Config not found'}), 404
        with open(target_path, 'r') as f:
            content = f.read()
        content = re.sub(r"server\s*\{.*?listen\s+443\s+ssl;.*?\}", "", content, flags=re.DOTALL)
        content = re.sub(r"server\s*\{[^}]*listen\s+80;[^}]*?(?:return\s+301\s+https://|\.well-known/acme-challenge)[^}]*\}", "", content, flags=re.DOTALL)
        ok_write, err_write = write_conf_file(domain, content)
        if not ok_write:
            return jsonify({'success': False, 'error': err_write}), 500
        success, message = reload_nginx()
        return jsonify({'success': success, 'message': message})
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

@app.route('/api/nginx/ssl/letsencrypt-log/<domain>')
@login_required
def api_letsencrypt_log(domain):
    """Stream Let's Encrypt generation logs via SSE while running certbot inside nginx container."""
    try:
        if not is_valid_site_name(domain):
            return Response("data: Invalid domain\n\n", mimetype='text/event-stream')
        email = request.args.get('email') or os.environ.get('LE_EMAIL')
        if not email:
            return Response("data: Email is required for Let's Encrypt\n\n", mimetype='text/event-stream')
        try:
            subprocess.run(['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', 'mkdir -p /var/www/certbot/.well-known/acme-challenge'], capture_output=True, text=True, timeout=20)
        except Exception:
            pass
        cmd = [
            'docker', 'exec', NGINX_CONTAINER_NAME, 'certbot', 'certonly',
            '--webroot', '-w', '/var/www/certbot',
            '-d', domain, '--email', email,
            '--agree-tos', '--non-interactive', '--preferred-challenges', 'http',
            '--debug'
        ]
        log_dir = '/app/tmp'
        try:
            os.makedirs(log_dir, exist_ok=True)
        except Exception:
            pass
        log_path = os.path.join(log_dir, f"letsencrypt_{domain}_{int(time.time())}.log")
        ssl_dir, cert_path, key_path = _ssl_paths(domain)
        os.makedirs(ssl_dir, exist_ok=True)
        def generate():
            yield f"data: Running: {' '.join(cmd)}\n\n"
            try:
                with open(log_path, 'w') as lf:
                    lf.write(f"Running: {' '.join(cmd)}\n")
                    proc = subprocess.Popen(
                        cmd,
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
                    lf.write(f"\nCertbot finished with code {rc}\n")
                    status = 'error'
                    if rc == 0:
                        try:
                            src_fullchain = f"{NGINX_CONTAINER_NAME}:/etc/letsencrypt/live/{domain}/fullchain.pem"
                            src_privkey = f"{NGINX_CONTAINER_NAME}:/etc/letsencrypt/live/{domain}/privkey.pem"
                            cp_cert = subprocess.run(['docker', 'cp', src_fullchain, cert_path], capture_output=True, text=True, timeout=20)
                            yield f"data: Copying fullchain.pem: rc={cp_cert.returncode} err={(cp_cert.stderr or '').strip()}\n\n"
                            cp_key = subprocess.run(['docker', 'cp', src_privkey, key_path], capture_output=True, text=True, timeout=20)
                            yield f"data: Copying privkey.pem: rc={cp_key.returncode} err={(cp_key.stderr or '').strip()}\n\n"
                            if cp_cert.returncode == 0 and cp_key.returncode == 0:
                                status = 'success'
                            else:
                                # If copy failed, check if certs are present in container and allow success
                                check_cmd = [
                                    'docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c',
                                    f"test -f /etc/letsencrypt/live/{domain}/fullchain.pem && test -f /etc/letsencrypt/live/{domain}/privkey.pem && echo OK || echo MISSING"
                                ]
                                chk = subprocess.run(check_cmd, capture_output=True, text=True, timeout=10)
                                if chk.returncode == 0 and 'OK' in (chk.stdout or ''):
                                    status = 'success'
                                    yield f"data: Certs exist in container; proceeding without host copy.\n\n"
                                else:
                                    status = 'error'
                        except Exception as ce:
                            yield f"data: Error copying certs: {str(ce)}\n\n"
                            status = 'error'
                    yield "event: done\n"
                    yield f"data: {status}\n\n"
            except Exception as e:
                try:
                    with open(log_path, 'a') as lf:
                        lf.write(f"\nCertbot error: {str(e)}\n")
                except Exception:
                    pass
                yield f"data: Certbot error: {str(e)}\n\n"
                yield "event: done\n"
        return Response(generate(), mimetype='text/event-stream')
    except Exception as e:
        return Response(f"data: Internal error: {str(e)}\n\n", mimetype='text/event-stream')

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


# --- MySQL Configuration Endpoints ---
@app.route('/api/mysql/get-config', methods=['GET'])
@login_required
def get_mysql_config_file():
    """Read MySQL configuration from inside the Docker container."""
    try:
        # Prefer the primary my.cnf if present (effective config), then common defaults
        candidate_files = [
            '/etc/my.cnf',
            '/etc/mysql/my.cnf',
            '/etc/mysql/mysql.conf.d/mysqld.cnf',
            '/etc/mysql/mariadb.conf.d/50-server.cnf',
            '/etc/mysql/conf.d/99-custom.cnf'
        ]
        chosen_path = None
        for path in candidate_files:
            try:
                check = subprocess.run(['docker', 'exec', MYSQL_CONTAINER_NAME, 'test', '-f', path], capture_output=True, text=True)
                if check.returncode == 0:
                    chosen_path = path
                    break
            except Exception:
                continue
        if not chosen_path:
            # If no file, pick a writable directory for future saves
            dir_candidates = ['/etc/mysql/conf.d', '/etc/mysql/mysql.conf.d', '/etc/mysql/mariadb.conf.d', '/etc']
            for d in dir_candidates:
                check = subprocess.run(['docker', 'exec', MYSQL_CONTAINER_NAME, 'test', '-d', d], capture_output=True, text=True)
                if check.returncode == 0:
                    chosen_path = os.path.join(d, '99-custom.cnf')
                    break
        if not chosen_path:
            return jsonify({'success': False, 'error': 'No suitable MySQL config location found in container'}), 404
        # Read content if file exists; otherwise provide a minimal template
        read_cmd = ['docker', 'exec', MYSQL_CONTAINER_NAME, 'bash', '-lc', f"if [ -f '{chosen_path}' ]; then cat '{chosen_path}'; fi"]
        read_result = subprocess.run(read_cmd, capture_output=True, text=True)
        content = read_result.stdout if read_result.returncode == 0 else ''
        if not content.strip():
            content = '[mysqld]\n'
        return jsonify({'success': True, 'path': chosen_path, 'config': content})
    except Exception as e:
        logger.error(f"Error reading MySQL config: {e}")
        return jsonify({'success': False, 'error': str(e)}), 500

@app.route('/api/mysql/save-config', methods=['POST'])
@login_required
def save_mysql_config_file():
    """Save MySQL configuration into the container and restart MySQL."""
    try:
        data = request.get_json(silent=True) or {}
        config_content = data.get('config')
        if not isinstance(config_content, str) or not config_content.strip():
            return jsonify({'success': False, 'error': 'Config content is required'}), 400
        # Determine target path: prefer /etc/my.cnf if present
        target_path = None
        check_my = subprocess.run(['docker', 'exec', MYSQL_CONTAINER_NAME, 'test', '-f', '/etc/my.cnf'], capture_output=True, text=True)
        if check_my.returncode == 0:
            target_path = '/etc/my.cnf'
        else:
            # Fallback to conf directories
            dir_candidates = ['/etc/mysql/conf.d', '/etc/mysql/mysql.conf.d', '/etc/mysql/mariadb.conf.d', '/etc']
            for d in dir_candidates:
                check = subprocess.run(['docker', 'exec', MYSQL_CONTAINER_NAME, 'test', '-d', d], capture_output=True, text=True)
                if check.returncode == 0:
                    target_path = f"{d}/99-custom.cnf"
                    break
        if not target_path:
            return jsonify({'success': False, 'error': 'MySQL config location not found in container'}), 404
        ts = int(time.time())
        backup_and_write = (
            f"if [ -f '{target_path}' ]; then cp '{target_path}' '{target_path}.backup.{ts}'; fi; "
            f"cat > '{target_path}' <<'EOF'\n{config_content}\nEOF"
        )
        write_cmd = ['docker', 'exec', '-i', MYSQL_CONTAINER_NAME, 'bash', '-lc', backup_and_write]
        write_result = subprocess.run(write_cmd, capture_output=True, text=True)
        if write_result.returncode != 0:
            return jsonify({'success': False, 'error': write_result.stderr or write_result.stdout}), 500
        # Restart MySQL by restarting the container
        restart_result = subprocess.run(['docker', 'restart', MYSQL_CONTAINER_NAME], capture_output=True, text=True)
        if restart_result.returncode != 0:
            return jsonify({'success': False, 'error': f"Failed to restart MySQL container: {restart_result.stderr or restart_result.stdout}"}), 500
        time.sleep(3)
        # Optional verification
        try:
            connection = create_mysql_connection()
            cursor = connection.cursor(dictionary=True)
            cursor.execute('SHOW VARIABLES LIKE "log_bin"')
            lb = cursor.fetchone()
            cursor.execute('SHOW VARIABLES LIKE "server_id"')
            sid = cursor.fetchone()
            logger.info(f"Post-save config: log_bin={lb}, server_id={sid}")
            cursor.close()
            connection.close()
        except Exception as ve:
            logger.warning(f"MySQL verification failed after config save: {ve}")
        return jsonify({'success': True, 'message': f"Configuration saved to {target_path} and MySQL restarted"})
    except Exception as e:
        logger.error(f"Error saving MySQL config: {e}")
        return jsonify({'success': False, 'error': str(e)}), 500

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

@app.route('/api/mysql-info')
@login_required
def mysql_info():
    try:
        connection = create_mysql_connection()
        connection.autocommit = True
        connection.cmd_query('USE mysql')
        cursor = connection.cursor()
        cursor.execute('SHOW VARIABLES LIKE "%version%"')
        version = cursor.fetchall()
        cursor.execute('SHOW STATUS')
        status = cursor.fetchall()
        cursor.close()
        connection.close()
        # Safely convert list-of-lists to dict, only using pairs
        def pairs_to_dict(rows):
            out = {}
            for r in rows:
                try:
                    if isinstance(r, (list, tuple)) and len(r) >= 2:
                        out[str(r[0])] = r[1]
                except Exception:
                    continue
            return out
        return jsonify({
            'version': pairs_to_dict(version),
            'status': pairs_to_dict(status)
        })
    except Exception as e:
        logger.error(f'MySQL info error: {e}')
        return jsonify({'error': str(e)}), 500

# MySQL Database Management
@app.route('/api/mysql/execute-query', methods=['POST'])
@login_required
def execute_query():
    logger.info('Database query execution requested')
    try:
        data = request.get_json()
        database = data.get('database')
        query = data.get('query')

        if not database or not query:
            return jsonify({'error': 'Database and query are required'}), 400

        connection = create_mysql_connection()
        connection.cmd_query(f'USE `{database}`')

        cursor = connection.cursor(dictionary=True)
        cursor.execute(query)

        if query.strip().upper().startswith(('SELECT', 'SHOW', 'DESCRIBE', 'EXPLAIN')):
            result = cursor.fetchall()
        else:
            connection.commit()
            result = [{'affected_rows': cursor.rowcount}]

        cursor.close()
        connection.close()

        return jsonify(result)

    except Exception as err:
        logger.error(f'MySQL error executing query: {err}')
        return jsonify({'error': str(err)}), 500
    except Exception as e:
        logger.error(f'Error executing query: {e}')
        return jsonify({'error': 'Internal server error'}), 500

@app.route('/api/mysql/tables')
@login_required
def list_tables():
    logger.info('Table list requested')
    connection = None
    cursor = None
    try:
        db_name = request.args.get('database')
        if not db_name:
            logger.warning('Database name not provided')
            return jsonify({'error': 'Database name is required'}), 400
        # Validate database name (prevent SQL injection)
        if not re.match(r'^[a-zA-Z0-9_]+$', db_name):
            logger.warning(f'Invalid database name format: {db_name}')
            return jsonify({'error': 'Invalid database name format'}), 400
        try:
            connection = create_mysql_connection()
            connection.cmd_query(f'USE `{db_name}`')
            cursor = connection.cursor(dictionary=True)
            # Test connection
            cursor.execute('SELECT 1')
            cursor.fetchone()
        except Exception as e:
            logger.error(f'Database connection error: {str(e)}')
            return jsonify({'error': 'Database connection error'}), 500
        try:
            # Get list of tables with information
            cursor.execute("""
                SELECT 
                    table_name as name,
                    `table_rows` as `rows`,
                    ROUND((data_length + index_length) / 1024 / 1024, 2) as size
                FROM information_schema.tables 
                WHERE table_schema = %s
                AND table_type = 'BASE TABLE'
            """, (db_name,))
            table_list = cursor.fetchall()
            # Handle null values
            for table in table_list:
                table['rows'] = table['rows'] if table['rows'] is not None else 0
                table['size'] = table['size'] if table['size'] is not None else 0
        except Exception as e:
            logger.error(f'Error listing tables: {str(e)}')
            raise
        return jsonify(table_list)
    except Exception as e:
        errno = getattr(e, 'errno', None)
        msg = getattr(e, 'msg', str(e))
        error_msg = f'MySQL Error ({errno}): {msg}' if errno is not None else f'Unexpected error: {str(e)}'
        logger.error(f'Error listing tables: {error_msg}')
        return jsonify({'error': error_msg}), 500
    except Exception as e:
        error_msg = f'Unexpected error: {str(e)}'
        logger.error(f'Error listing tables: {error_msg}')
        return jsonify({'error': error_msg}), 500
    finally:
        if cursor:
            try:
                cursor.close()
            except Exception as e:
                logger.error(f'Error closing cursor: {str(e)}')
        if connection:
            try:
                connection.close()
            except Exception as e:
                logger.error(f'Error closing connection: {str(e)}')

@app.route('/api/mysql/databases')
@login_required
def list_databases():
    logger.info('Database list requested')
    try:
        connection = create_mysql_connection()
        cursor = connection.cursor()
        # Get list of databases
        cursor.execute('SHOW DATABASES')
        databases = cursor.fetchall()
        # Get size of each database
        db_list = []
        for row in databases:
            # Support both list-of-lists and list-of-tuples
            db_name = row[0] if isinstance(row, (list, tuple)) and row else row
            if db_name not in ['information_schema', 'performance_schema', 'mysql', 'sys']:
                cursor.execute(f"""
                    SELECT 
                        ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) 
                    FROM information_schema.tables 
                    WHERE table_schema = '{db_name}'
                    GROUP BY table_schema;
                """)
                size_row = cursor.fetchone()
                size_val = None
                if isinstance(size_row, dict):
                    # dictionary cursor case
                    size_val = list(size_row.values())[0] if size_row else None
                elif isinstance(size_row, (list, tuple)):
                    size_val = size_row[0] if size_row else None
                else:
                    size_val = size_row
                db_list.append({
                    'name': db_name,
                    'size': float(size_val) if size_val is not None else 0
                })
        cursor.close()
        connection.close()
        return jsonify(db_list)
    except Exception as e:
        logger.error(f'List databases error: {e}')
        return jsonify({'error': str(e)}), 500

@app.route('/api/mysql/create-db', methods=['POST'])
@login_required
def create_database():
    logger.info('Database creation attempt')
    try:
        data = request.json
        db_name = data.get('name')
        db_user = data.get('user')
        db_password = data.get('password')
        
        if not db_name:
            logger.warning('Database name not provided')
            return jsonify({'error': 'Nama database diperlukan'}), 400

        # Validate database name (prevent SQL injection)
        if not re.match(r'^[a-zA-Z0-9_]+$', db_name):
            logger.warning(f'Invalid database name format: {db_name}')
            return jsonify({'error': 'Format nama database tidak valid. Gunakan hanya huruf, angka, dan underscore'}), 400

        # Validate username if provided
        if db_user and not re.match(r'^[a-zA-Z0-9_]+$', db_user):
            logger.warning(f'Invalid username format: {db_user}')
            return jsonify({'error': 'Format username tidak valid. Gunakan hanya huruf, angka, dan underscore'}), 400

        connection = create_mysql_connection()
        cursor = connection.cursor()

        # Create database
        cursor.execute(f'CREATE DATABASE `{db_name}`')
        
        # Create user and grant privileges if username and password provided
        if db_user and db_password:
            try:
                # Check if user already exists
                cursor.execute("SELECT User FROM mysql.user WHERE User = %s AND Host = 'localhost'", (db_user,))
                user_exists = cursor.fetchone()
                
                if user_exists:
                    # User exists, just grant privileges
                    cursor.execute(f"GRANT ALL PRIVILEGES ON `{db_name}`.* TO '{db_user}'@'localhost'")
                    cursor.execute("FLUSH PRIVILEGES")
                    logger.info(f'Granted privileges on {db_name} to existing user {db_user}')
                else:
                    # Create new user
                    cursor.execute(f"CREATE USER '{db_user}'@'localhost' IDENTIFIED BY %s", (db_password,))
                    # Grant all privileges on the database to the user
                    cursor.execute(f"GRANT ALL PRIVILEGES ON `{db_name}`.* TO '{db_user}'@'localhost'")
                    cursor.execute("FLUSH PRIVILEGES")
                    logger.info(f'Created new user {db_user} and granted privileges on {db_name}')
            except Exception as user_err:
                logger.warning(f'Error handling user {db_user}: {user_err}')
                # Continue without failing the database creation
                pass
            
        # Check if user exists before closing connection
        user_created_or_exists = False
        if db_user:
            try:
                cursor.execute("SELECT User FROM mysql.user WHERE User = %s AND Host = 'localhost'", (db_user,))
                user_created_or_exists = cursor.fetchone() is not None
            except Exception:
                user_created_or_exists = False
        
        connection.commit()
        cursor.close()
        connection.close()
        
        if db_user:
            if user_created_or_exists:
                logger.info(f'Database {db_name} created and privileges granted to user {db_user}')
                return jsonify({'message': f'Database "{db_name}" berhasil dibuat dan privileges diberikan ke user "{db_user}"'})
            else:
                logger.info(f'Database {db_name} created successfully')
                return jsonify({'message': f'Database "{db_name}" berhasil dibuat (user gagal dibuat)'})
        else:
            logger.info(f'Database {db_name} created successfully')
            return jsonify({'message': f'Database "{db_name}" berhasil dibuat'})
    except Exception as err:
        logger.error(f'MySQL error during database creation: {err}')
        errno = getattr(err, 'errno', None)
        if errno == 1007:  # Database already exists
            return jsonify({'error': f'Database "{db_name}" sudah ada'}), 400
        elif errno == 1045:  # Access denied
            return jsonify({'error': 'Akses ditolak. Periksa konfigurasi MySQL'}), 500
        else:
            return jsonify({'error': f'Error MySQL: {str(err)}'}), 500
    except Exception as e:
        logger.error(f'Unexpected error during database creation: {e}')
        return jsonify({'error': f'Terjadi kesalahan: {str(e)}'}), 500

@app.route('/api/mysql/delete-db', methods=['POST'])
@login_required
def delete_database():
    logger.info('Database deletion attempt')
    try:
        db_name = request.json.get('name')
        if not db_name:
            logger.warning('Database name not provided for deletion')
            return jsonify({'error': 'Nama database diperlukan'}), 400

        # Validate database name (prevent SQL injection)
        if not re.match(r'^[a-zA-Z0-9_]+$', db_name):
            logger.warning(f'Invalid database name format for deletion: {db_name}')
            return jsonify({'error': 'Format nama database tidak valid'}), 400

        connection = create_mysql_connection()
        cursor = connection.cursor()

        # Drop database
        cursor.execute(f'DROP DATABASE `{db_name}`')
        connection.commit()
        cursor.close()
        connection.close()
        
        logger.info(f'Database {db_name} deleted successfully')
        return jsonify({'message': f'Database "{db_name}" berhasil dihapus'})
    except Exception as err:
        logger.error(f'MySQL error during database deletion: {err}')
        errno = getattr(err, 'errno', None)
        if errno == 1008:  # Database doesn't exist
            return jsonify({'error': f'Database "{db_name}" tidak ditemukan'}), 400
        elif errno == 1045:  # Access denied
            return jsonify({'error': 'Akses ditolak. Periksa konfigurasi MySQL'}), 500
        else:
            return jsonify({'error': f'Error MySQL: {str(err)}'}), 500
    except Exception as e:
        logger.error(f'Unexpected error during database deletion: {e}')
        return jsonify({'error': f'Terjadi kesalahan: {str(e)}'}), 500

@app.route('/api/mysql/delete-table', methods=['POST'])
@login_required
def delete_table():
    logger.info('Table deletion requested')
    try:
        data = request.get_json()
        db_name = data.get('database')
        table_name = data.get('table')
        
        if not db_name or not table_name:
            logger.warning('Database name or table name not provided')
            return jsonify({'error': 'Nama database dan tabel diperlukan'}), 400
        
        # Validate database and table names (prevent SQL injection)
        if not re.match(r'^[a-zA-Z0-9_]+$', db_name):
            logger.warning(f'Invalid database name format for table deletion: {db_name}')
            return jsonify({'error': 'Format nama database tidak valid'}), 400
            
        if not re.match(r'^[a-zA-Z0-9_]+$', table_name):
            logger.warning(f'Invalid table name format for deletion: {table_name}')
            return jsonify({'error': 'Format nama tabel tidak valid'}), 400

        connection = create_mysql_connection()
        cursor = connection.cursor()

        # Use the specified database
        cursor.execute(f'USE `{db_name}`')
        
        # Drop table
        cursor.execute(f'DROP TABLE `{table_name}`')
        connection.commit()
        cursor.close()
        connection.close()
        
        logger.info(f'Table {table_name} deleted successfully from database {db_name}')
        return jsonify({'message': f'Tabel "{table_name}" berhasil dihapus dari database "{db_name}"'})
    except Exception as err:
        logger.error(f'MySQL error during table deletion: {err}')
        errno = getattr(err, 'errno', None)
        if errno == 1051:  # Table doesn't exist
            return jsonify({'error': f'Tabel "{table_name}" tidak ditemukan dalam database "{db_name}"'}), 400
        elif errno == 1049:  # Database doesn't exist
            return jsonify({'error': f'Database "{db_name}" tidak ditemukan'}), 400
        elif errno == 1045:  # Access denied
            return jsonify({'error': 'Akses ditolak. Periksa konfigurasi MySQL'}), 500
        else:
            return jsonify({'error': f'Error MySQL: {str(err)}'}), 500
    except Exception as e:
        logger.error(f'Unexpected error during table deletion: {e}')
        return jsonify({'error': f'Terjadi kesalahan: {str(e)}'}), 500

@app.route('/api/mysql/get-root-password', methods=['GET'])
@login_required
def get_root_password():
    """Return current root password from db_config (do not query MySQL)."""
    try:
        pwd = db_config.get('password', '')
        return jsonify({'password': pwd or ''})
    except Exception as e:
        logger.error(f'Error getting root password: {e}')
        return jsonify({'error': 'Failed to get root password'}), 500

@app.route('/api/mysql/set-root-password', methods=['POST'])
@login_required
def set_root_password():
    logger.info('Root password change requested')
    try:
        data = request.get_json()
        new_password = data.get('password', '')
        
        # Connect to MySQL as root with current password from db_config
        connection = create_mysql_connection()
        cursor = connection.cursor()
        
        # Set new password for root user
        if new_password:
            cursor.execute("ALTER USER 'root'@'localhost' IDENTIFIED BY %s", (new_password,))
        else:
            cursor.execute("ALTER USER 'root'@'localhost' IDENTIFIED BY ''")
        
        connection.commit()
        cursor.close()
        connection.close()
        
        # Update the global db_config with new password
        global db_config
        old_password = db_config['password']
        db_config['password'] = new_password
        logger.info(f'Password updated in db_config: from "{old_password}" to "{new_password}"')
        logger.info(f'Current db_config password: {db_config["password"]}')
        
        # Save configuration to JSON file for persistence
        if save_mysql_config(db_config):
            logger.info('Password configuration saved to file successfully')
        else:
            logger.warning('Failed to save password configuration to file')
        
        # Reinitialize connection pool with new password
        global connection_pool
        try:
            if connection_pool:
                connection_pool._remove_connections()
            connection_pool = mysql.connector.pooling.MySQLConnectionPool(**db_config)
            logger.info('Connection pool reinitialized with new password')
        except Exception as e:
            logger.warning(f'Failed to reinitialize connection pool: {str(e)}')
            connection_pool = None
        
        # Restart SLEMP to reload database configuration
        try:
            logger.info('Restarting SLEMP to reload database configuration')
            restart_result = subprocess.run(['supervisorctl', 'restart', 'slemp'], capture_output=True, text=True, timeout=30)
            if restart_result.returncode == 0:
                logger.info('SLEMP restarted successfully')
            else:
                logger.warning(f'Failed to restart SLEMP: {restart_result.stderr}')
        except Exception as e:
            logger.warning(f'Error restarting SLEMP: {str(e)}')
        
        logger.info('Root password changed successfully')
        return jsonify({'message': 'Password root MySQL berhasil diubah'})
        
    except Exception as err:
        logger.error(f'MySQL error during password change: {err}')
        errno = getattr(err, 'errno', None)
        if errno == 1045:  # Access denied
            return jsonify({'error': 'Akses ditolak. Password root saat ini mungkin sudah berubah'}), 500
        else:
            return jsonify({'error': f'Error MySQL: {str(err)}'}), 500
    except Exception as e:
        logger.error(f'Unexpected error during password change: {e}')
        return jsonify({'error': f'Terjadi kesalahan: {str(e)}'}), 500

@app.route('/api/mysql/users', methods=['GET'])
@login_required
def get_mysql_users():
    """Get list of MySQL users"""
    try:
        connection = create_mysql_connection()
        cursor = connection.cursor(dictionary=True)
        
        # Get all users
        cursor.execute("SELECT User, Host FROM mysql.user WHERE User != '' ORDER BY User, Host")
        users = cursor.fetchall()
        
        # Get privileges for each user
        for user in users:
            try:
                cursor.execute(f"SHOW GRANTS FOR '{user['User']}'@'{user['Host']}'")
                grants = cursor.fetchall()
                privileges = []
                for grant in grants:
                    grant_text = list(grant.values())[0]
                    if 'ALL PRIVILEGES' in grant_text:
                        privileges.append('ALL PRIVILEGES')
                    elif 'GRANT' in grant_text:
                        # Extract specific privileges
                        start = grant_text.find('GRANT ') + 6
                        end = grant_text.find(' ON')
                        if start < end:
                            privileges.append(grant_text[start:end])
                user['privileges'] = ', '.join(privileges) if privileges else 'No privileges'
            except Exception:
                user['privileges'] = 'Unable to determine'
        
        cursor.close()
        connection.close()
        
        return jsonify({'users': users})
        
    except Exception as err:
        logger.error(f'MySQL error getting users: {err}')
        return jsonify({'error': f'Error MySQL: {str(err)}'}), 500
    except Exception as e:
        logger.error(f'Error getting MySQL users: {str(e)}')
        return jsonify({'error': f'Terjadi kesalahan: {str(e)}'}), 500

@app.route('/api/mysql/create-user', methods=['POST'])
@login_required
def create_mysql_user():
    """Create a new MySQL user"""
    try:
        data = request.get_json()
        username = data.get('username')
        password = data.get('password')
        host = data.get('host', '%')
        privileges = data.get('privileges', 'ALL')
        
        if not username or not password:
            return jsonify({'error': 'Username dan password harus diisi'}), 400
        
        connection = create_mysql_connection()
        cursor = connection.cursor()
        
        # Create user
        cursor.execute(f"CREATE USER '{username}'@'{host}' IDENTIFIED BY %s", (password,))
        
        # Grant privileges
        if privileges == 'ALL':
            cursor.execute(f"GRANT ALL PRIVILEGES ON *.* TO '{username}'@'{host}'")
        else:
            cursor.execute(f"GRANT {privileges} ON *.* TO '{username}'@'{host}'")
        
        # Flush privileges
        cursor.execute("FLUSH PRIVILEGES")
        
        connection.commit()
        cursor.close()
        connection.close()
        
        logger.info(f'MySQL user created: {username}@{host}')
        return jsonify({'message': f'User {username}@{host} berhasil dibuat'})
        
    except Exception as err:
        logger.error(f'MySQL error creating user: {err}')
        errno = getattr(err, 'errno', None)
        if errno == 1396:  # User already exists
            return jsonify({'error': f'User {username}@{host} sudah ada'}), 400
        else:
            return jsonify({'error': f'Error MySQL: {str(err)}'}), 500
    except Exception as e:
        logger.error(f'Error creating MySQL user: {str(e)}')
        return jsonify({'error': f'Terjadi kesalahan: {str(e)}'}), 500

@app.route('/api/mysql/delete-user', methods=['DELETE'])
@login_required
def delete_mysql_user():
    """Delete a MySQL user"""
    try:
        data = request.get_json()
        username = data.get('username')
        host = data.get('host')
        
        if not username or not host:
            return jsonify({'error': 'Username dan host harus diisi'}), 400
        
        # Prevent deletion of root user
        if username == 'root':
            return jsonify({'error': 'User root tidak dapat dihapus'}), 400
        
        connection = create_mysql_connection()
        cursor = connection.cursor()
        
        # Drop user
        cursor.execute(f"DROP USER '{username}'@'{host}'")
        
        # Flush privileges
        cursor.execute("FLUSH PRIVILEGES")
        
        connection.commit()
        cursor.close()
        connection.close()
        
        logger.info(f'MySQL user deleted: {username}@{host}')
        return jsonify({'message': f'User {username}@{host} berhasil dihapus'})
        
    except Exception as err:
        logger.error(f'MySQL error deleting user: {err}')
        errno = getattr(err, 'errno', None)
        if errno == 1396:  # User doesn't exist
            return jsonify({'error': f'User {username}@{host} tidak ditemukan'}), 404
        else:
            return jsonify({'error': f'Error MySQL: {str(err)}'}), 500
    except Exception as e:
        logger.error(f'Error deleting MySQL user: {str(e)}')
        return jsonify({'error': f'Terjadi kesalahan: {str(e)}'}), 500

@app.route('/api/mysql/update-user', methods=['PUT'])
@login_required
def update_mysql_user():
    """Update a MySQL user's password, privileges, and host"""
    try:
        data = request.get_json()
        username = data.get('username')
        host = data.get('host')
        new_host = data.get('new_host', host)  # Use original host if new_host not provided
        password = data.get('password')
        privileges = data.get('privileges', 'ALL PRIVILEGES')
        
        if not username or not host or not password or not new_host:
            return jsonify({'error': 'Username, host, new_host, dan password harus diisi'}), 400
        
        connection = create_mysql_connection()
        cursor = connection.cursor()
        
        # If host is changing, we need to create a new user and drop the old one
        if host != new_host:
            # Create new user with new host
            cursor.execute(f"CREATE USER '{username}'@'{new_host}' IDENTIFIED BY %s", (password,))
            
            # Grant privileges to new user
            if privileges == 'ALL PRIVILEGES':
                cursor.execute(f"GRANT ALL PRIVILEGES ON *.* TO '{username}'@'{new_host}'")
            else:
                cursor.execute(f"GRANT {privileges} ON *.* TO '{username}'@'{new_host}'")
            
            # Drop old user
            try:
                cursor.execute(f"DROP USER '{username}'@'{host}'")
            except Exception as err:
                errno = getattr(err, 'errno', None)
                if errno != 1396:  # Ignore if user doesn't exist
                    raise
            
            logger.info(f'MySQL user host changed: {username}@{host} -> {username}@{new_host}')
            message = f'User {username}@{new_host} berhasil diupdate (host diubah dari {host})'
        else:
            # Just update password and privileges for existing user
            cursor.execute(f"ALTER USER '{username}'@'{host}' IDENTIFIED BY %s", (password,))
            
            # Update privileges if specified
            if privileges and privileges != 'CURRENT':
                # First revoke all privileges
                cursor.execute(f"REVOKE ALL PRIVILEGES ON *.* FROM '{username}'@'{host}'")
                
                # Grant new privileges
                if privileges == 'ALL PRIVILEGES':
                    cursor.execute(f"GRANT ALL PRIVILEGES ON *.* TO '{username}'@'{host}'")
                else:
                    cursor.execute(f"GRANT {privileges} ON *.* TO '{username}'@'{host}'")
            
            logger.info(f'MySQL user updated: {username}@{host}')
            message = f'User {username}@{host} berhasil diupdate'
        
        # Flush privileges
        cursor.execute("FLUSH PRIVILEGES")
        
        connection.commit()
        cursor.close()
        connection.close()
        
        return jsonify({'message': message})
        
    except Exception as err:
        logger.error(f'MySQL error updating user: {err}')
        errno = getattr(err, 'errno', None)
        if errno == 1396:  # User doesn't exist
            return jsonify({'error': f'User {username}@{host} tidak ditemukan'}), 404
        elif errno == 1007:  # User already exists
            return jsonify({'error': f'User {username}@{new_host} sudah ada'}), 409
        else:
            return jsonify({'error': f'Error MySQL: {str(err)}'}), 500
    except Exception as e:
        logger.error(f'Error updating MySQL user: {str(e)}')
        return jsonify({'error': f'Terjadi kesalahan: {str(e)}'}), 500

# MySQL Replication Management
@app.route('/api/mysql/replication/status', methods=['GET'])
@login_required
def get_replication_status():
    """Get MySQL replication status"""
    try:
        connection = create_mysql_connection()
        if not connection:
            return jsonify({'success': False, 'error': 'Failed to connect to MySQL'})
        
        cursor = connection.cursor(dictionary=True)
        status = {'master': None, 'slave': []}
        
        # Check master status
        try:
            cursor.execute("SHOW MASTER STATUS")
            master_result = cursor.fetchone()
            if master_result:
                status['master'] = {
                    'file': master_result.get('File'),
                    'position': master_result.get('Position'),
                    'binlog_do_db': master_result.get('Binlog_Do_DB'),
                    'binlog_ignore_db': master_result.get('Binlog_Ignore_DB')
                }
        except Exception as e:
            logger.debug(f"Master status check failed: {e}")
            pass  # Master not configured
        
        # Check slave status
        try:
            cursor.execute("SHOW SLAVE STATUS")
            slave_result = cursor.fetchone()
            if slave_result:
                status['slave'] = [{
                    'master_host': slave_result.get('Master_Host'),
                    'master_user': slave_result.get('Master_User'),
                    'master_port': slave_result.get('Master_Port'),
                    'slave_io_running': slave_result.get('Slave_IO_Running'),
                    'slave_sql_running': slave_result.get('Slave_SQL_Running'),
                    'seconds_behind_master': slave_result.get('Seconds_Behind_Master'),
                    'master_log_file': slave_result.get('Master_Log_File'),
                    'read_master_log_pos': slave_result.get('Read_Master_Log_Pos'),
                    'relay_log_file': slave_result.get('Relay_Log_File'),
                    'relay_log_pos': slave_result.get('Relay_Log_Pos'),
                    'last_errno': slave_result.get('Last_Errno'),
                    'last_error': slave_result.get('Last_Error')
                }]
        except Exception as e:
            logger.debug(f"Slave status check failed: {e}")
            pass  # Slave not configured
        
        cursor.close()
        connection.close()
        
        return jsonify({'success': True, 'status': status})
        
    except Exception as e:
        logger.error(f'Error getting replication status: {str(e)}')
        return jsonify({'success': False, 'error': str(e)})

@app.route('/api/mysql/replication/setup-master', methods=['POST'])
@login_required
def setup_mysql_master():
    """Setup MySQL as master for replication (inside Docker container)."""
    try:
        data = request.get_json()
        server_id = data.get('server_id', 1)
        log_bin = data.get('log_bin', 'mysql-bin')

        if not isinstance(server_id, int) or server_id < 1 or server_id > 4294967295:
            return jsonify({'success': False, 'error': 'Invalid server ID. Must be between 1 and 4294967295'})

        # Prefer editing /etc/my.cnf to ensure effective config, else fallback to conf.d drop-in
        use_mycnf = subprocess.run(['docker', 'exec', MYSQL_CONTAINER_NAME, 'test', '-f', '/etc/my.cnf'], capture_output=True, text=True).returncode == 0
        cfg_name = '99-replication.cnf'
        cfg_content = (
            "[mysqld]\n"
            f"server-id = {server_id}\n"
            f"log-bin = {log_bin}\n"
            "binlog-format = ROW\n"
            "expire-logs-days = 7\n"
        )

        if use_mycnf:
            # Ensure conf.d exists and is included by my.cnf; also remove disabling directives
            shell_script = (
                "set -e; "
                # Backup my.cnf
                "ts=$(date +%s); cp /etc/my.cnf /etc/my.cnf.backup.$ts; "
                # Ensure conf.d directory exists
                "mkdir -p /etc/mysql/conf.d; "
                # Remove disabling directives and conflicting lines
                "sed -i -E '/^\\s*(disable-log-bin|skip-log-bin)\\b/d' /etc/my.cnf; "
                "sed -i -E '/^\\s*server-id\\s*=.*/d' /etc/my.cnf; "
                "sed -i -E '/^\\s*log-bin\\s*=.*/d' /etc/my.cnf; "
                # Ensure !includedir directive present
                "grep -qE '^\\s*!includedir\\s+/etc/mysql/conf.d' /etc/my.cnf || echo '\n!includedir /etc/mysql/conf.d' >> /etc/my.cnf; "
                # Write drop-in replication config
                f"cat > /etc/mysql/conf.d/{cfg_name} <<'EOF'\n{cfg_content}EOF"
            )
            write_result = subprocess.run(['docker', 'exec', '-i', MYSQL_CONTAINER_NAME, 'bash', '-lc', shell_script], capture_output=True, text=True)
            if write_result.returncode != 0:
                return jsonify({'success': False, 'error': f"Failed to update /etc/my.cnf and write replication config: {write_result.stderr or write_result.stdout}"})
            cfg_path = f"/etc/mysql/conf.d/{cfg_name}"
        else:
            # Fallback: write drop-in to an available conf directory
            candidate_dirs = [
                '/etc/mysql/mariadb.conf.d',
                '/etc/mysql/mysql.conf.d',
                '/etc/mysql/conf.d'
            ]
            target_dir = None
            for d in candidate_dirs:
                try:
                    check = subprocess.run(['docker', 'exec', MYSQL_CONTAINER_NAME, 'test', '-d', d], capture_output=True, text=True)
                    if check.returncode == 0:
                        target_dir = d
                        break
                except Exception:
                    continue
            if not target_dir:
                return jsonify({'success': False, 'error': 'MySQL config directory not found in container'})
            cfg_path = f"{target_dir}/{cfg_name}"
            write_cmd = ['docker', 'exec', '-i', MYSQL_CONTAINER_NAME, 'bash', '-lc', f"cat > '{cfg_path}' <<'EOF'\n{cfg_content}EOF"]
            write_result = subprocess.run(write_cmd, capture_output=True, text=True)
            if write_result.returncode != 0:
                return jsonify({'success': False, 'error': f"Failed to write replication config: {write_result.stderr or write_result.stdout}"})

        # Restart MySQL by restarting the container
        restart_result = subprocess.run(['docker', 'restart', MYSQL_CONTAINER_NAME], capture_output=True, text=True)
        if restart_result.returncode != 0:
            return jsonify({'success': False, 'error': f"Failed to restart MySQL container: {restart_result.stderr}"})
        # Wait a bit for MySQL to come up
        time.sleep(3)

        # Verify variables before master status
        connection = create_mysql_connection()
        cursor = connection.cursor(dictionary=True)
        try:
            cursor.execute('SHOW VARIABLES LIKE "log_bin"')
            lb = cursor.fetchone() or {}
            cursor.execute('SHOW VARIABLES LIKE "server_id"')
            sid = cursor.fetchone() or {}
            lb_val = (lb.get('Value') or lb.get('value') or '').upper()
            sid_val = int((sid.get('Value') or sid.get('value') or 0))
            logger.info(f"After setup-master: log_bin={lb_val}, server_id={sid_val}, cfg_path={cfg_path}")
            if lb_val != 'ON' or sid_val == 0:
                cursor.close(); connection.close()
                return jsonify({'success': False, 'error': f'Master not active: log_bin={lb_val}, server_id={sid_val}. Config at {cfg_path}.'})
        except Exception as e:
            logger.warning(f"Variable check failed: {e}")

        # Check master status
        try:
            cursor.execute("SHOW MASTER STATUS")
            master_status = cursor.fetchone()
        except Exception as e:
            master_status = None
            logger.error(f"Error checking master status: {e}")

        if not master_status:
            cursor.close()
            connection.close()
            return jsonify({'success': False, 'error': 'Master setup failed - no master status found (ensure binlog is enabled)'});

        # Create replication user if provided
        replication_user = data.get('replication_user')
        replication_password = data.get('replication_password')
        logger.info(f'Received replication_user: {replication_user}, has_password: {bool(replication_password)}')
        if replication_user and replication_password:
            try:
                # Check if user exists first
                cursor.execute("SELECT User FROM mysql.user WHERE User = %s AND Host = '%'", (replication_user,))
                user_exists = cursor.fetchone() is not None
                if user_exists:
                    cursor.execute(f"DROP USER '{replication_user}'@'%'")
                cursor.execute(f"CREATE USER '{replication_user}'@'%' IDENTIFIED BY '{replication_password}'")
                cursor.execute(f"GRANT REPLICATION SLAVE ON *.* TO '{replication_user}'@'%'")
                cursor.execute("FLUSH PRIVILEGES")
                logger.info(f'Replication user {replication_user} prepared')
            except Exception as user_error:
                logger.error(f'Failed to create replication user {replication_user}: {str(user_error)}')
                # Don't fail the entire setup if user creation fails
        else:
            logger.warning(f'Replication user not created - user: {replication_user}, password provided: {bool(replication_password)}')

        cursor.close()
        connection.close()

        logger.info(f'MySQL master setup completed inside container with server-id {server_id}')
        return jsonify({
            'success': True,
            'message': 'Master setup completed successfully',
            'master_status': master_status,
            'replication_user_created': bool(replication_user and replication_password)
        })

    except Exception as e:
        logger.error(f'Error setting up MySQL master: {str(e)}')
        return jsonify({'success': False, 'error': str(e)})

@app.route('/api/mysql/replication/setup-slave', methods=['POST'])
@login_required
def setup_mysql_slave():
    """Setup MySQL as slave for replication"""
    try:
        data = request.get_json()
        master_host = data.get('master_host')
        master_user = data.get('master_user')
        master_password = data.get('master_password')
        master_log_file = data.get('master_log_file', '')
        master_log_pos = data.get('master_log_pos', 0)
        server_id = data.get('server_id', 2)
        
        if not all([master_host, master_user, master_password]):
            return jsonify({'success': False, 'error': 'Master host, user, and password are required'})
        
        if not isinstance(server_id, int) or server_id < 1 or server_id > 4294967295:
            return jsonify({'success': False, 'error': 'Invalid server ID. Must be between 1 and 4294967295'})
        
        # Write slave config inside container and restart via Docker
        container = MYSQL_CONTAINER_NAME
        script_template = r"""
set -euo pipefail
TS=$(date +%s)
FILES=( "/etc/my.cnf" "/etc/mysql/my.cnf" "/etc/mysql/mysql.conf.d/mysqld.cnf" )
DIRS=( "/etc/mysql/conf.d" "/etc/mysql/mysql.conf.d" )
sanitize_file() {
  local f="$1"
  [ -f "$f" ] || return 0
  cp "$f" "$f.backup.$TS" || true
  sed -E '/^[[:space:]]*(server[-_]?id|relay[-_]?log|read[-_]?only)[[:space:]]*(=|$)/d' "$f" > "$f.tmp" && mv "$f.tmp" "$f"
}
for f in "${FILES[@]}"; do sanitize_file "$f"; done
for d in "${DIRS[@]}"; do
  if [ -d "$d" ]; then
    for cf in "$d"/*.cnf; do [ -f "$cf" ] || continue; sanitize_file "$cf"; done
  fi
done
mkdir -p /etc/mysql/conf.d || true
cat > /etc/mysql/conf.d/99-slave.cnf <<EOC
[mysqld]
server-id = __SERVER_ID__
relay-log = relay-bin
read-only = 1
EOC
"""
        script = script_template.replace("__SERVER_ID__", str(server_id))
        exec_res = subprocess.run(
            ['docker','exec',container,'bash','-lc', f"cat >/tmp/setup_slave.sh <<'SCRIPT_EOF'\n{script}\nSCRIPT_EOF\nbash /tmp/setup_slave.sh"],
            capture_output=True, text=True
        )
        if exec_res.returncode != 0:
            return jsonify({'success': False, 'error': f"Failed to update slave config in container: {exec_res.stderr or exec_res.stdout}"})
        restart_res = subprocess.run(['docker','restart',container], capture_output=True, text=True)
        if restart_res.returncode != 0:
            return jsonify({'success': False, 'error': f"Failed to restart container: {restart_res.stderr or restart_res.stdout}"})
        time.sleep(3)
        
        # Tunggu MySQL siap setelah restart dengan retry
        max_attempts = 10
        attempt = 0
        connection = None
        while attempt < max_attempts:
            connection = create_mysql_connection()
            if connection:
                break
            attempt += 1
            time.sleep(1)
        if not connection:
            return jsonify({'success': False, 'error': 'Failed to connect to MySQL after restart (timeout waiting for server ready)'})
        
        cursor = connection.cursor()
        
        # Stop slave jika sedang berjalan (abaikan error jika sudah berhenti)
        try:
            cursor.execute("STOP SLAVE")
        except Exception as stop_error:
            if "Slave already has been stopped" not in str(stop_error):
                logger.warning(f'Error stopping slave (ignored): {str(stop_error)}')
        
        # Validasi master host dapat dijangkau dari dalam container
        try:
            subprocess.run(['docker','exec',container,'bash','-lc', f"nc -z -w2 {master_host} 3306"], capture_output=True, text=True, timeout=5)
        except Exception as host_check_err:
            logger.warning(f"Master host reachability check failed (ignored): {host_check_err}")
        
        # Bangun SQL CHANGE MASTER TO (sertakan MASTER_PORT)
        change_master_sql = f"""
        CHANGE MASTER TO
        MASTER_HOST='{master_host}',
        MASTER_USER='{master_user}',
        MASTER_PASSWORD='{master_password}',
        MASTER_PORT=3306
        """
        
        if master_log_file and master_log_pos > 0:
            change_master_sql += f",\nMASTER_LOG_FILE='{master_log_file}',\nMASTER_LOG_POS={master_log_pos}"
        
        # Eksekusi CHANGE MASTER TO dengan penanganan error detail
        # Tambah delay 10 detik sebelum CHANGE MASTER TO untuk memastikan MySQL siap
        time.sleep(10)
        try:
            cursor.execute(change_master_sql)
        except Exception as cm_err:
            logger.error(f"CHANGE MASTER TO failed: {cm_err}; sql={change_master_sql}")
            cursor.close()
            connection.close()
            return jsonify({'success': False, 'error': f'CHANGE MASTER TO failed: {cm_err}'})
        
        # Start slave dengan penanganan error
        try:
            cursor.execute("START SLAVE")
        except Exception as start_err:
            logger.warning(f"START SLAVE error: {start_err}")
        
        # Beri waktu lebih lama agar slave benar-benar mulai
        time.sleep(5)
        
        # Check status slave
        cursor.close()
        connection.close()
        
        conn2 = create_mysql_connection()
        if not conn2:
            logger.warning('Slave setup completed, but cannot reconnect to verify status')
            return jsonify({'success': True, 'message': 'Slave setup completed successfully (verification skipped: reconnect failed)'})
        try:
            cur2 = conn2.cursor(dictionary=True)
            cur2.execute("SHOW SLAVE STATUS")
            status = cur2.fetchone()
            cur2.close()
        finally:
            conn2.close()
        
        if not status:
            logger.info('Slave setup completed, but SHOW SLAVE STATUS returns empty (not configured)')
            return jsonify({'success': False, 'error': 'Slave is not configured after setup', 'status': None})
        
        io = str(status.get('Slave_IO_Running') or status.get('slave_io_running') or '')
        sql = str(status.get('Slave_SQL_Running') or status.get('slave_sql_running') or '')
        is_running = (io == 'Yes' and sql == 'Yes')
        
        if not is_running:
            last_error = status.get('Last_Error') or status.get('Last_SQL_Error') or status.get('Last_IO_Error')
            logger.warning(f"Slave setup verification failed: IO={io}, SQL={sql}, error={last_error}")
            return jsonify({'success': False, 'error': f"Slave not running after setup (IO={io}, SQL={sql})", 'last_error': last_error, 'status': status})
        
        logger.info(f'MySQL slave setup completed and verified running for master {master_host}')
        return jsonify({'success': True, 'message': 'Slave setup completed successfully', 'status': status})
        
    except Exception as e:
        logger.error(f'Error setting up MySQL slave: {str(e)}')
        return jsonify({'success': False, 'error': str(e)})

@app.route('/api/mysql/replication/start-slave', methods=['POST'])
@login_required
def start_mysql_slave():
    """Start MySQL slave replication and verify status"""
    try:
        connection = create_mysql_connection()
        if not connection:
            return jsonify({'success': False, 'error': 'Failed to connect to MySQL'})
        
        cursor = connection.cursor()
        cursor.execute("START SLAVE")
        cursor.close()
        connection.close()
        
        # Beri waktu agar slave benar-benar mulai
        time.sleep(2)
        
        # Cek status slave
        conn2 = create_mysql_connection()
        if not conn2:
            logger.warning('Started slave but cannot reconnect to verify status')
            return jsonify({'success': True, 'message': 'Slave started successfully (verification skipped: reconnect failed)'})
        try:
            cur2 = conn2.cursor(dictionary=True)
            cur2.execute("SHOW SLAVE STATUS")
            status = cur2.fetchone()
            cur2.close()
        finally:
            conn2.close()
        
        if not status:
            logger.info('Slave started, but SHOW SLAVE STATUS returns empty (not configured)')
            return jsonify({'success': False, 'error': 'Slave not configured or status unavailable after start', 'status': None})
        
        io = str(status.get('Slave_IO_Running') or status.get('slave_io_running') or '')
        sql = str(status.get('Slave_SQL_Running') or status.get('slave_sql_running') or '')
        is_running = (io == 'Yes' and sql == 'Yes')
        
        if not is_running:
            last_error = status.get('Last_Error') or status.get('Last_SQL_Error') or status.get('Last_IO_Error')
            logger.warning(f"Slave start verification failed: IO={io}, SQL={sql}, error={last_error}")
            return jsonify({'success': False, 'error': f"Slave not running after START SLAVE (IO={io}, SQL={sql})", 'last_error': last_error, 'status': status})
        
        logger.info('MySQL slave started and verified running')
        return jsonify({'success': True, 'message': 'Slave started successfully', 'status': status})
        
    except Exception as e:
        logger.error(f'Error starting MySQL slave: {str(e)}')
        return jsonify({'success': False, 'error': str(e)})

@app.route('/api/mysql/replication/stop-slave', methods=['POST'])
@login_required
def stop_mysql_slave():
    """Stop MySQL slave replication"""
    try:
        connection = create_mysql_connection()
        if not connection:
            return jsonify({'success': False, 'error': 'Failed to connect to MySQL'})
        
        cursor = connection.cursor()
        try:
            cursor.execute("STOP SLAVE")
            logger.info('MySQL slave stopped')
            message = 'Slave stopped successfully'
        except Exception as stop_error:
            if "Slave already has been stopped" in str(stop_error):
                logger.info('MySQL slave was already stopped')
                message = 'Slave was already stopped'
            else:
                raise stop_error
        
        cursor.close()
        connection.close()
        
        return jsonify({'success': True, 'message': message})
        
    except Exception as e:
        logger.error(f'Error stopping MySQL slave: {str(e)}')
        return jsonify({'success': False, 'error': str(e)})

@app.route('/api/mysql/replication/reset-slave', methods=['POST'])
@login_required
def reset_mysql_slave():
    """Reset MySQL slave configuration and clean slave-related config inside Docker container"""
    try:
        # First reset the slave inside MySQL
        connection = create_mysql_connection()
        if not connection:
            return jsonify({'success': False, 'error': 'Failed to connect to MySQL'})
        
        cursor = connection.cursor()
        try:
            cursor.execute("STOP SLAVE")
        except Exception as stop_error:
            if "Slave already has been stopped" not in str(stop_error):
                logger.warning(f'Error stopping slave during reset (ignored): {str(stop_error)}')
        
        cursor.execute("RESET SLAVE ALL")
        cursor.close()
        connection.close()

        # Then clean slave-related configuration files inside the container and restart MySQL
        container = MYSQL_CONTAINER_NAME
        script = r"""
set -euo pipefail
TS=$(date +%s)
FILES=( "/etc/my.cnf" "/etc/mysql/my.cnf" "/etc/mysql/mysql.conf.d/mysqld.cnf" "/etc/mysql/mariadb.conf.d/50-server.cnf" )
DIRS=( "/etc/mysql/conf.d" "/etc/mysql/mysql.conf.d" "/etc/mysql/mariadb.conf.d" )
sanitize_file() {
  local f="$1"
  [ -f "$f" ] || return 0
  cp "$f" "$f.backup.$TS" || true
  sed -E '/^[[:space:]]*(server[-_]?id|relay[-_]?log|read[-_]?only)[[:space:]]*(=|$)/d' "$f" > "$f.tmp" && mv "$f.tmp" "$f"
}
for f in "${FILES[@]}"; do sanitize_file "$f"; done
for d in "${DIRS[@]}"; do
  if [ -d "$d" ]; then
    rm -f "$d"/*slave*.cnf || true
    rm -f "$d"/99-slave.cnf || true
    rm -f "$d"/*replication*.cnf || true
    for cf in "$d"/*.cnf; do [ -f "$cf" ] || continue; sanitize_file "$cf"; done
  fi
done
"""
        exec_res = subprocess.run(
            ['docker','exec',container,'bash','-lc', f"cat >/tmp/reset_slave.sh <<'SCRIPT_EOF'\n{script}\nSCRIPT_EOF\nbash /tmp/reset_slave.sh"],
            capture_output=True, text=True
        )
        if exec_res.returncode != 0:
            return jsonify({'success': False, 'error': f"Failed to clean slave config in container: {exec_res.stderr or exec_res.stdout}"})
        restart_res = subprocess.run(['docker','restart',container], capture_output=True, text=True)
        if restart_res.returncode != 0:
            return jsonify({'success': False, 'error': f"Failed to restart container: {restart_res.stderr or restart_res.stdout}"})
        time.sleep(3)

        # Verify that slave status is cleared (optional)
        connection2 = create_mysql_connection()
        slave_status = None
        if connection2:
            try:
                cur2 = connection2.cursor(dictionary=True)
                cur2.execute("SHOW SLAVE STATUS")
                slave_status = cur2.fetchone()
                cur2.close()
            finally:
                connection2.close()
        
        logger.info('MySQL slave reset and configuration cleaned')
        return jsonify({'success': True, 'message': 'Slave reset and configuration cleaned successfully', 'slave_status': slave_status})
        
    except Exception as e:
        logger.error(f'Error resetting MySQL slave: {str(e)}')
        return jsonify({'success': False, 'error': str(e)})

@app.route('/api/mysql/replication/skip-error', methods=['POST'])
@login_required
def skip_mysql_replication_error():
    """Skip current MySQL replication error by advancing one transaction.
    Intended for errors like 1062 (duplicate entry).
    Steps: STOP SLAVE; SET GLOBAL sql_slave_skip_counter = 1; START SLAVE; verify.
    """
    try:
        connection = create_mysql_connection()
        if not connection:
            return jsonify({'success': False, 'error': 'Failed to connect to MySQL'})
        
        # Perform skip sequence
        cur = connection.cursor()
        try:
            try:
                cur.execute("STOP SLAVE")
            except Exception as e_stop:
                # If already stopped, continue
                if "already has been stopped" not in str(e_stop):
                    logger.warning(f"STOP SLAVE returned: {e_stop}")
            
            # Advance one transaction
            cur.execute("SET GLOBAL sql_slave_skip_counter = 1")
            
            # Restart slave
            cur.execute("START SLAVE")
        finally:
            cur.close()
            connection.close()
        
        # Small delay to let slave apply
        time.sleep(2)
        
        # Verify status
        conn2 = create_mysql_connection()
        status = None
        last_error = None
        if conn2:
            try:
                c2 = conn2.cursor(dictionary=True)
                c2.execute("SHOW SLAVE STATUS")
                status = c2.fetchone()
                c2.close()
            finally:
                conn2.close()
        if status:
            io = str(status.get('Slave_IO_Running') or status.get('slave_io_running') or '')
            sql = str(status.get('Slave_SQL_Running') or status.get('slave_sql_running') or '')
            last_error = status.get('Last_Error') or status.get('Last_SQL_Error') or status.get('Last_IO_Error')
            is_running = (io == 'Yes' and sql == 'Yes')
            if not is_running:
                logger.warning(f"Skip error attempted, but slave not running: IO={io}, SQL={sql}, error={last_error}")
                return jsonify({'success': False, 'error': 'Slave still not running after skip', 'last_error': last_error, 'status': status})
        
        logger.info('Replication error skipped and slave running (if configured)')
        return jsonify({'success': True, 'message': 'Replication error skipped. Slave restarted.', 'status': status})
    except Exception as e:
        logger.error(f'Error skipping replication error: {str(e)}')
        return jsonify({'success': False, 'error': str(e)})

@app.route('/api/mysql/replication/disable-master', methods=['POST'])
@login_required
def disable_mysql_master():
    """Disable MySQL/MariaDB master (turn off binary logging) inside Docker container"""
    try:
        container = MYSQL_CONTAINER_NAME
        # Shell script to sanitize all relevant config files and ensure binlog off
        script = r"""
set -euo pipefail
TS=$(date +%s)
# Detect flavor (MariaDB vs MySQL)
FLAVOR="mysql"
if mysql --version 2>/dev/null | grep -qi mariadb; then FLAVOR="mariadb"; fi
# Candidate files and directories
FILES=(
  "/etc/my.cnf"
  "/etc/mysql/my.cnf"
  "/etc/mysql/mysql.conf.d/mysqld.cnf"
  "/etc/mysql/mariadb.conf.d/50-server.cnf"
)
DIRS=("/etc/mysql/conf.d" "/etc/mysql/mysql.conf.d" "/etc/mysql/mariadb.conf.d")
# Function to sanitize config files by removing replication/binlog directives
sanitize_file() {
  local f="$1"
  [ -f "$f" ] || return 0
  cp "$f" "$f.backup.$TS" || true
  sed -E '/^[[:space:]]*(server[-_]?id|log[-_]?bin|binlog[-_]?format|expire[-_]?logs[-_]?days|binlog[-_]?do[-_]?db|binlog[-_]?ignore[-_]?db)[[:space:]]*(=|$)/d' "$f" > "$f.tmp" && mv "$f.tmp" "$f"
}
# Process candidate files
for f in "${FILES[@]}"; do sanitize_file "$f"; done
# Process drop-in directories: remove replication-related cnf files and scrub others
for d in "${DIRS[@]}"; do
  if [ -d "$d" ]; then
    # Remove specific replication config files if any
    rm -f "$d"/*replication*.cnf || true
    rm -f "$d"/99-replication.cnf || true
    # Scrub all *.cnf under this dir
    for cf in "$d"/*.cnf; do
      [ -f "$cf" ] || continue
      sanitize_file "$cf"
    done
  fi
done
# For MariaDB, explicitly disable binlog to be safe
if [ "$FLAVOR" = "mariadb" ]; then
  TARGET="/etc/my.cnf"
  [ -f "$TARGET" ] || TARGET="/etc/mysql/my.cnf"
  if [ -f "$TARGET" ]; then
    cp "$TARGET" "$TARGET.backup.$TS" || true
    sed -E '/^[[:space:]]*(log[-_]?bin|binlog[-_]?format)[[:space:]]*(=|$)/d' "$TARGET" > "$TARGET.tmp" && mv "$TARGET.tmp" "$TARGET"
    if ! grep -qi '^\[mysqld\]' "$TARGET"; then
      printf '[mysqld]\n' >> "$TARGET"
    fi
    printf 'disable-log-bin\n' >> "$TARGET"
  else
    cat > "$TARGET" <<EOC
[mysqld]
disable-log-bin
EOC
  fi
else
  # For MySQL, remove log_bin directives and ensure disabling via skip-log-bin
  TARGET="/etc/mysql/mysql.conf.d/mysqld.cnf"
  if [ -f "$TARGET" ]; then
    cp "$TARGET" "$TARGET.backup.$TS" || true
    sed -E '/^[[:space:]]*log[-_]?bin[[:space:]]*(=|$)/d' "$TARGET" > "$TARGET.tmp" && mv "$TARGET.tmp" "$TARGET"
  fi
  mkdir -p /etc/mysql/conf.d || true
  cat > /etc/mysql/conf.d/99-disable-binlog.cnf <<EOC
[mysqld]
skip-log-bin
EOC
fi
"""
        exec_res = subprocess.run(
            ['docker', 'exec', container, 'bash', '-lc', f"cat >/tmp/disable_master.sh <<'SCRIPT_EOF'\n{script}\nSCRIPT_EOF\nbash /tmp/disable_master.sh"],
            capture_output=True, text=True
        )
        if exec_res.returncode != 0:
            return jsonify({'success': False, 'error': f"Failed to update config in container: {exec_res.stderr or exec_res.stdout}"})
        # Restart MySQL container to apply changes
        restart_res = subprocess.run(['docker', 'restart', container], capture_output=True, text=True)
        if restart_res.returncode != 0:
            return jsonify({'success': False, 'error': f"Failed to restart container: {restart_res.stderr or restart_res.stdout}"})
        time.sleep(3)
        # Verify that binary logging is OFF
        connection = create_mysql_connection()
        if not connection:
            return jsonify({'success': False, 'error': 'Failed to connect to MySQL after restart'})
        cursor = connection.cursor(dictionary=True)
        try:
            cursor.execute('SHOW VARIABLES LIKE "log_bin"')
            lb = cursor.fetchone()
            log_bin_state = (lb or {}).get('Value') or (lb or {}).get('value')
        except Exception as e:
            log_bin_state = None
        # Also check master status emptiness
        master_empty = True
        try:
            cursor.execute("SHOW MASTER STATUS")
            mr = cursor.fetchone()
            if mr:
                master_empty = False
        except Exception:
            master_empty = True
        # Attempt reset if still enabled
        if (log_bin_state and str(log_bin_state).upper() == 'ON') or (not master_empty):
            try:
                cursor.execute("RESET MASTER")
                cursor.execute('SHOW VARIABLES LIKE "log_bin"')
                lb2 = cursor.fetchone()
                log_bin_state = (lb2 or {}).get('Value') or (lb2 or {}).get('value')
                cursor.execute("SHOW MASTER STATUS")
                mr2 = cursor.fetchone()
                master_empty = not bool(mr2)
            except Exception:
                pass
        cursor.close()
        connection.close()
        if (log_bin_state and str(log_bin_state).upper() == 'ON') or (not master_empty):
            return jsonify({'success': False, 'error': 'Binary logging is still ON; check other config files enabling log-bin.'})
        logger.info('MySQL master replication disabled successfully (log_bin=OFF)')
        return jsonify({'success': True, 'message': 'Master replication disabled successfully'})
    
    except Exception as e:
        logger.error(f'Error disabling MySQL master: {str(e)}')
        return jsonify({'success': False, 'error': str(e)})

@app.route('/api/mysql/error-logs', methods=['GET'])
def get_mysql_error_logs():
    """Get MySQL error logs"""
    try:
        # Common MySQL error log paths
        error_log_paths = [
            '/var/log/mysql/error.log',
            '/var/log/mysqld.log',
            '/var/log/mysql.err',
            '/usr/local/var/mysql/*.err'
        ]
        
        logs_content = ""
        log_found = False
        
        for log_path in error_log_paths:
            if '*' in log_path:
                # Handle wildcard paths
                import glob
                matching_files = glob.glob(log_path)
                for file_path in matching_files:
                    if os.path.exists(file_path):
                        try:
                            with open(file_path, 'r') as f:
                                # Get last 100 lines
                                lines = f.readlines()
                                recent_lines = lines[-100:] if len(lines) > 100 else lines
                                logs_content += f"\n=== {file_path} ===\n"
                                logs_content += ''.join(recent_lines)
                                log_found = True
                        except Exception as e:
                            logs_content += f"\nError reading {file_path}: {str(e)}\n"
            else:
                if os.path.exists(log_path):
                    try:
                        with open(log_path, 'r') as f:
                            # Get last 100 lines
                            lines = f.readlines()
                            recent_lines = lines[-100:] if len(lines) > 100 else lines
                            logs_content += f"\n=== {log_path} ===\n"
                            logs_content += ''.join(recent_lines)
                            log_found = True
                    except Exception as e:
                        logs_content += f"\nError reading {log_path}: {str(e)}\n"
        
        if not log_found:
            logs_content = "No MySQL error logs found in common locations."
        
        # Format logs for HTML display
        formatted_logs = logs_content.replace('\n', '<br>').replace(' ', '&nbsp;')
        
        return jsonify({
            'success': True,
            'logs': formatted_logs
        })
        
    except Exception as e:
        logger.error(f'Error getting MySQL error logs: {str(e)}')
        return jsonify({'success': False, 'message': str(e)})

@app.route('/api/mysql/replication-logs', methods=['GET'])
def get_mysql_replication_logs():
    """Get MySQL replication-related logs"""
    try:
        connection = create_mysql_connection()
        if not connection:
            return jsonify({'success': False, 'message': 'Failed to connect to MySQL'})
        
        cursor = connection.cursor(dictionary=True)
        logs_content = ""
        
        # Get replication status for error information
        try:
            cursor.execute("SHOW SLAVE STATUS")
            slave_status = cursor.fetchone()
            
            if slave_status:
                logs_content += "=== Slave Status Information ===\n"
                logs_content += f"Slave_IO_Running: {slave_status.get('Slave_IO_Running', 'N/A')}\n"
                logs_content += f"Slave_SQL_Running: {slave_status.get('Slave_SQL_Running', 'N/A')}\n"
                logs_content += f"Last_IO_Error: {slave_status.get('Last_IO_Error', 'None')}\n"
                logs_content += f"Last_SQL_Error: {slave_status.get('Last_SQL_Error', 'None')}\n"
                logs_content += f"Seconds_Behind_Master: {slave_status.get('Seconds_Behind_Master', 'N/A')}\n"
                logs_content += f"Master_Log_File: {slave_status.get('Master_Log_File', 'N/A')}\n"
                logs_content += f"Read_Master_Log_Pos: {slave_status.get('Read_Master_Log_Pos', 'N/A')}\n\n"
            else:
                logs_content += "=== No Slave Configuration Found ===\n\n"
        except Exception as e:
            logs_content += f"Error getting slave status: {str(e)}\n\n"
        
        # Get binary log information
        try:
            cursor.execute("SHOW BINARY LOGS")
            binary_logs = cursor.fetchall()
            
            if binary_logs:
                logs_content += "=== Binary Logs ===\n"
                for log in binary_logs:
                    logs_content += f"{log.get('Log_name', 'N/A')} - Size: {log.get('File_size', 'N/A')} bytes\n"
                logs_content += "\n"
            else:
                logs_content += "=== No Binary Logs Found ===\n\n"
        except Exception as e:
            logs_content += f"Binary logs not available: {str(e)}\n\n"
        
        # Get master status
        try:
            cursor.execute("SHOW MASTER STATUS")
            master_status = cursor.fetchone()
            
            if master_status:
                logs_content += "=== Master Status ===\n"
                logs_content += f"File: {master_status.get('File', 'N/A')}\n"
                logs_content += f"Position: {master_status.get('Position', 'N/A')}\n"
                logs_content += f"Binlog_Do_DB: {master_status.get('Binlog_Do_DB', 'N/A')}\n"
                logs_content += f"Binlog_Ignore_DB: {master_status.get('Binlog_Ignore_DB', 'N/A')}\n\n"
            else:
                logs_content += "=== No Master Configuration Found ===\n\n"
        except Exception as e:
            logs_content += f"Master status not available: {str(e)}\n\n"
        
        cursor.close()
        connection.close()
        
        if not logs_content.strip():
            logs_content = "No replication information available."
        
        # Format logs for HTML display
        formatted_logs = logs_content.replace('\n', '<br>').replace(' ', '&nbsp;')
        
        return jsonify({
            'success': True,
            'logs': formatted_logs
        })
        
    except Exception as e:
        logger.error(f'Error getting MySQL replication logs: {str(e)}')
        return jsonify({'success': False, 'message': str(e)})

@app.route('/api/mysql/general-logs', methods=['GET'])
def get_mysql_general_logs():
    """Get MySQL general logs"""
    try:
        # Common MySQL general log paths
        general_log_paths = [
            '/var/log/mysql/mysql.log',
            '/var/log/mysql/general.log',
            '/usr/local/var/mysql/general.log'
        ]
        
        logs_content = ""
        log_found = False
        
        for log_path in general_log_paths:
            if os.path.exists(log_path):
                try:
                    with open(log_path, 'r') as f:
                        # Get last 100 lines
                        lines = f.readlines()
                        recent_lines = lines[-100:] if len(lines) > 100 else lines
                        logs_content += f"\n=== {log_path} ===\n"
                        logs_content += ''.join(recent_lines)
                        log_found = True
                except Exception as e:
                    logs_content += f"\nError reading {log_path}: {str(e)}\n"
        
        if not log_found:
            logs_content = "No MySQL general logs found. General logging may be disabled.\n\n"
            logs_content += "To enable general logging, add the following to your MySQL configuration:\n"
            logs_content += "general_log = 1\n"
            logs_content += "general_log_file = /var/log/mysql/general.log"
        
        # Format logs for HTML display
        formatted_logs = logs_content.replace('\n', '<br>').replace(' ', '&nbsp;')
        
        return jsonify({
            'success': True,
            'logs': formatted_logs
        })
        
    except Exception as e:
        logger.error(f'Error getting MySQL general logs: {str(e)}')
        return jsonify({'success': False, 'message': str(e)})

@app.route('/api/mysql/slow-logs', methods=['GET'])
def get_mysql_slow_logs():
    """Get MySQL slow query logs"""
    try:
        # Common MySQL slow log paths
        slow_log_paths = [
            '/var/log/mysql/mysql-slow.log',
            '/var/log/mysql/slow.log',
            '/usr/local/var/mysql/slow.log'
        ]
        
        logs_content = ""
        log_found = False
        
        for log_path in slow_log_paths:
            if os.path.exists(log_path):
                try:
                    with open(log_path, 'r') as f:
                        # Get last 50 entries (slow logs can be verbose)
                        lines = f.readlines()
                        recent_lines = lines[-200:] if len(lines) > 200 else lines
                        logs_content += f"\n=== {log_path} ===\n"
                        logs_content += ''.join(recent_lines)
                        log_found = True
                except Exception as e:
                    logs_content += f"\nError reading {log_path}: {str(e)}\n"
        
        if not log_found:
            logs_content = "No MySQL slow query logs found. Slow query logging may be disabled.\n\n"
            logs_content += "To enable slow query logging, add the following to your MySQL configuration:\n"
            logs_content += "slow_query_log = 1\n"
            logs_content += "slow_query_log_file = /var/log/mysql/mysql-slow.log\n"
            logs_content += "long_query_time = 2"
        
        # Format logs for HTML display
        formatted_logs = logs_content.replace('\n', '<br>').replace(' ', '&nbsp;')
        
        return jsonify({
            'success': True,
            'logs': formatted_logs
        })
        
    except Exception as e:
        logger.error(f'Error getting MySQL slow logs: {str(e)}')
        return jsonify({'success': False, 'message': str(e)})

@app.route('/api/mysql/analyze-slow-logs', methods=['GET'])
def analyze_mysql_slow_logs():
    """Analyze MySQL slow query logs"""
    try:
        connection = create_mysql_connection()
        if not connection:
            return jsonify({'success': False, 'message': 'Failed to connect to MySQL'})
        
        cursor = connection.cursor(dictionary=True)
        analysis_content = ""
        
        # Get slow query log status
        try:
            cursor.execute("SHOW VARIABLES LIKE 'slow_query_log'")
            slow_log_status = cursor.fetchone()
            
            cursor.execute("SHOW VARIABLES LIKE 'long_query_time'")
            long_query_time = cursor.fetchone()
            
            cursor.execute("SHOW VARIABLES LIKE 'slow_query_log_file'")
            slow_log_file = cursor.fetchone()
            
            analysis_content += "=== Slow Query Log Configuration ===\n"
            analysis_content += f"Slow Query Log: {slow_log_status.get('Value', 'N/A') if slow_log_status else 'N/A'}\n"
            analysis_content += f"Long Query Time: {long_query_time.get('Value', 'N/A') if long_query_time else 'N/A'} seconds\n"
            analysis_content += f"Slow Log File: {slow_log_file.get('Value', 'N/A') if slow_log_file else 'N/A'}\n\n"
            
        except Exception as e:
            analysis_content += f"Error getting slow log configuration: {str(e)}\n\n"
        
        # Get process list for currently running queries
        try:
            cursor.execute("SHOW PROCESSLIST")
            processes = cursor.fetchall()
            
            long_running = [p for p in processes if p.get('Time', 0) > 5 and p.get('Command') not in ['Sleep', 'Binlog Dump']]
            
            if long_running:
                analysis_content += "=== Currently Long Running Queries ===\n"
                for process in long_running:
                    analysis_content += f"ID: {process.get('Id', 'N/A')}, User: {process.get('User', 'N/A')}, "
                    analysis_content += f"Time: {process.get('Time', 'N/A')}s, State: {process.get('State', 'N/A')}\n"
                    analysis_content += f"Info: {process.get('Info', 'N/A')[:100]}...\n\n"
            else:
                analysis_content += "=== No Long Running Queries Found ===\n\n"
                
        except Exception as e:
            analysis_content += f"Error getting process list: {str(e)}\n\n"
        
        # Get query cache statistics
        try:
            cursor.execute("SHOW STATUS LIKE 'Qcache%'")
            qcache_stats = cursor.fetchall()
            
            if qcache_stats:
                analysis_content += "=== Query Cache Statistics ===\n"
                for stat in qcache_stats:
                    analysis_content += f"{stat.get('Variable_name', 'N/A')}: {stat.get('Value', 'N/A')}\n"
                analysis_content += "\n"
                
        except Exception as e:
            analysis_content += f"Query cache statistics not available: {str(e)}\n\n"
        
        # Get table lock statistics
        try:
            cursor.execute("SHOW STATUS LIKE 'Table_locks%'")
            lock_stats = cursor.fetchall()
            
            if lock_stats:
                analysis_content += "=== Table Lock Statistics ===\n"
                for stat in lock_stats:
                    analysis_content += f"{stat.get('Variable_name', 'N/A')}: {stat.get('Value', 'N/A')}\n"
                analysis_content += "\n"
                
        except Exception as e:
            analysis_content += f"Table lock statistics not available: {str(e)}\n\n"
        
        cursor.close()
        connection.close()
        
        if not analysis_content.strip():
            analysis_content = "No slow query analysis data available."
        
        # Format analysis for HTML display
        formatted_analysis = analysis_content.replace('\n', '<br>').replace(' ', '&nbsp;')
        
        return jsonify({
            'success': True,
            'analysis': formatted_analysis
        })
        
    except Exception as e:
        logger.error(f'Error analyzing MySQL slow logs: {str(e)}')
        return jsonify({'success': False, 'message': str(e)})

# ModSecurity per-site helpers

def _parse_modsecurity_status_from_content(content: str) -> tuple[str, bool]:
    """Parse ModSecurity status from vhost content.
    Only consider directives at server scope for server blocks that listen on 80 or 443.
    Ignore directives inside location blocks (e.g., ACME challenge should remain off).

    Returns (configured, effective_enabled):
    - configured: 'on'|'off'|'inherit' representing server-level configuration across 80/443 blocks
    - effective_enabled: True if ModSecurity effectively enabled (global default assumed on for 'inherit')
    """
    try:
        lines = content.splitlines()
        depth = 0
        in_server = False
        server_listen_ports = set()
        server_modsec = None  # 'on' | 'off' | None
        configs = []  # per targeted server block ('on'|'off'|'inherit')
        for line in lines:
            stripped = line.strip()
            # Enter server block
            if not in_server and re.search(r"\bserver\s*\{", line) and depth == 0:
                in_server = True
                server_listen_ports = set()
                server_modsec = None
                depth += line.count('{') - line.count('}')
                continue
            if in_server:
                # Detect listen ports only at server scope
                if depth == 1 and re.search(r"\blisten\s+(?:\S+:)?(80|443)\b", stripped):
                    try:
                        m = re.search(r"\blisten\s+(?:\S+:)?(80|443)\b", stripped)
                        if m:
                            server_listen_ports.add(m.group(1))
                    except Exception:
                        pass
                # Capture modsecurity directive only at server scope (ignore comments)
                if depth == 1 and (not stripped.startswith('#')):
                    if re.search(r"\bmodsecurity\s+on\s*;", stripped, re.IGNORECASE):
                        server_modsec = 'on'
                    elif re.search(r"\bmodsecurity\s+off\s*;", stripped, re.IGNORECASE):
                        server_modsec = 'off'
                # Update depth and finalize server block when closing
                depth += line.count('{') - line.count('}')
                if depth <= 0:
                    # End of server block; consider only if it listens on 80 or 443
                    if ('80' in server_listen_ports) or ('443' in server_listen_ports):
                        if server_modsec is None:
                            configs.append('inherit')
                        else:
                            configs.append(server_modsec)
                    in_server = False
            else:
                depth += line.count('{') - line.count('}')
        # Determine overall configured status across targeted server blocks
        if not configs:
            return 'inherit', True
        if all(c == 'on' for c in configs):
            return 'on', True
        if all(c == 'off' for c in configs):
            return 'off', False
        # Mixed or partial -> treat as inherit to avoid misleading UI
        return 'inherit', True
    except Exception:
        return 'inherit', True


def get_modsecurity_status(site_name: str) -> dict:
    ok, content, err = read_conf_file(site_name)
    if not ok:
        return {
            'site_name': site_name,
            'configured': 'inherit',
            'effective_enabled': True,
            'error': err
        }
    configured, effective = _parse_modsecurity_status_from_content(content)
    return {
        'site_name': site_name,
        'configured': configured,
        'effective_enabled': effective
    }


def _set_modsecurity_status_in_content(content: str, enabled: bool) -> str:
    """Update vhost content to set modsecurity on/off only at server scope; keep ACME location off."""
    lines = content.splitlines()
    directive = "modsecurity on;" if enabled else "modsecurity off;"
    new_lines = []
    depth = 0
    in_server = False
    for line in lines:
        stripped = line.strip()
        # Enter server block
        if not in_server and re.search(r"\bserver\s*\{", line) and depth == 0:
            in_server = True
            new_lines.append(line)
            depth += line.count('{') - line.count('}')
            # Insert directive at top-level of server block
            new_lines.append("    " + directive)
            continue
        if in_server:
            # Only remove existing modsecurity directive at server scope (depth==1)
            if depth == 1 and (not stripped.startswith('#')) and re.search(r"\bmodsecurity\s+(on|off)\s*;", stripped, flags=re.IGNORECASE):
                # Skip this line; we've inserted new directive already
                pass
            else:
                new_lines.append(line)
            depth += line.count('{') - line.count('}')
            if depth <= 0:
                in_server = False
        else:
            new_lines.append(line)
            depth += line.count('{') - line.count('}')
    return "\n".join(new_lines)


def update_modsecurity_status(site_name: str, enabled: bool) -> tuple[bool, str]:
    ok, content, err = read_conf_file(site_name)
    if not ok:
        return False, err
    backup_ok, backup_path = backup_conf_file(site_name)
    if not backup_ok:
        logger.warning(f"Failed to backup vhost for {site_name}: {backup_path}")
    new_content = _set_modsecurity_status_in_content(content, enabled)
    ok_w, err_w = write_conf_file(site_name, new_content)
    if not ok_w:
        return False, err_w
    success, message = reload_nginx()
    if not success:
        return False, f"Updated file but failed to reload nginx: {message}"
    return True, "ModSecurity status updated and nginx reloaded"


@app.route('/api/modsecurity/<site_name>', methods=['GET', 'POST'])
@login_required
def api_modsecurity_site(site_name):
    if not is_valid_site_name(site_name):
        return jsonify({'success': False, 'message': 'Invalid site name'}), 400
    if request.method == 'GET':
        status = get_modsecurity_status(site_name)
        return jsonify({'success': True, **status})
    else:
        data = request.get_json(silent=True) or {}
        enabled = bool(data.get('enabled', True))
        ok, msg = update_modsecurity_status(site_name, enabled)
        return jsonify({'success': ok, 'message': msg})




# --- ModSecurity Dashboard Helpers & API ---

MODSEC_AUDIT_PATH = '/var/log/modsecurity/audit.log'

def _parse_time(ts_str):
    try:
        if ts_str is None:
            return None
        # Numeric epoch (int/float)
        if isinstance(ts_str, (int, float)):
            return datetime.fromtimestamp(float(ts_str), tz=timezone.utc)
        s = str(ts_str).strip()
        # Numeric epoch string
        try:
            if s.replace('.', '', 1).isdigit():
                return datetime.fromtimestamp(float(s), tz=timezone.utc)
        except Exception:
            pass
        # Normalize Zulu and ensure timezone awareness
        s = s.replace('Z', '+00:00')
        # Try ISO formats with offset
        try:
            dt = datetime.fromisoformat(s)
            if dt.tzinfo is None:
                dt = dt.replace(tzinfo=timezone.utc)
            return dt.astimezone(timezone.utc)
        except Exception:
            pass
        # Try common ModSecurity format: 'Mon, 21 Aug 2023 10:14:23'
        try:
            dt = datetime.strptime(s[:19], '%a, %d %b %Y %H:%M:%S')
            return dt.replace(tzinfo=timezone.utc)
        except Exception:
            pass
        # Try ModSecurity ctime-like format: 'Tue Oct 14 18:18:15 2025'
        try:
            dt = datetime.strptime(s, '%a %b %d %H:%M:%S %Y')
            return dt.replace(tzinfo=timezone.utc)
        except Exception:
            pass
        # Try alternative ModSecurity format e.g. '2023-08-21 10:14:23'
        try:
            dt = datetime.strptime(s[:19], '%Y-%m-%d %H:%M:%S')
            return dt.replace(tzinfo=timezone.utc)
        except Exception:
            pass
        return None
    except Exception:
        return None

def read_modsec_audit_lines(max_lines: int = 5000):
    """Read ModSecurity audit lines with validation and container fallback.
    Returns a list of raw JSON lines. If both methods fail, returns [].
    """
    # Try local mounted file first
    try:
        if os.path.isfile(MODSEC_AUDIT_PATH):
            if not os.access(MODSEC_AUDIT_PATH, os.R_OK):
                logger.error(f"ModSec audit file not readable: {MODSEC_AUDIT_PATH}")
            else:
                try:
                    size = os.path.getsize(MODSEC_AUDIT_PATH)
                except Exception:
                    size = None
                logger.info(f"Reading local ModSec audit: path={MODSEC_AUDIT_PATH} size={size}")
                with open(MODSEC_AUDIT_PATH, 'r', encoding='utf-8', errors='ignore') as f:
                    lines = f.readlines()[-max_lines:]
                    return [ln.rstrip('\n') for ln in lines]
        else:
            logger.warning(f"ModSec audit file not found locally: {MODSEC_AUDIT_PATH}")
    except Exception as e:
        logger.warning(f"ModSec audit local read failed: {e}")
    # Fallback to docker exec into nginx container
    try:
        cmd = ['docker', 'exec', NGINX_CONTAINER_NAME, 'sh', '-c', f"tail -n {max_lines} {shlex.quote(MODSEC_AUDIT_PATH)} 2>/dev/null || true"]
        logger.info(f"Reading ModSec audit via docker exec: container={NGINX_CONTAINER_NAME} path={MODSEC_AUDIT_PATH}")
        res = subprocess.run(cmd, capture_output=True, text=True, timeout=10)
        if res.returncode != 0:
            logger.warning(f"docker exec returned code {res.returncode}: {res.stderr}")
        return (res.stdout or '').splitlines()
    except Exception as e:
        logger.error(f"Error reading ModSecurity audit log via docker exec: {e}")
        return []

def _parse_modsec_event(obj: dict) -> dict:
    tx = obj.get('transaction', {}) if isinstance(obj, dict) else {}
    req = tx.get('request', {})
    resp = tx.get('response', {})
    msgs = obj.get('messages', []) if isinstance(obj, dict) else []
    rule_ids = []
    severity = None
    try:
        for m in msgs:
            det = m.get('details', {})
            rid = det.get('rule_id') or det.get('id')
            if rid:
                rule_ids.append(str(rid))
            sev = det.get('severity') or m.get('severity')
            if sev and not severity:
                severity = sev
    except Exception:
        pass
    ts = tx.get('time_stamp') or obj.get('timestamp')
    ip = tx.get('client_ip') or obj.get('client_ip')
    method = req.get('method') or obj.get('method')
    uri = req.get('uri') or obj.get('uri')
    status = resp.get('status') or obj.get('status')
    # Extract domain from absolute URI or Host header
    domain = None
    try:
        if isinstance(uri, str) and (uri.startswith('http://') or uri.startswith('https://')):
            from urllib.parse import urlparse
            parsed = urlparse(uri)
            domain = parsed.hostname or parsed.netloc or None
        if not domain:
            headers = req.get('headers') or tx.get('request_headers') or {}
            host_val = None
            if isinstance(headers, dict):
                host_val = headers.get('Host') or headers.get('host') or headers.get('HOST')
            if not host_val:
                host_val = req.get('host') or tx.get('host')
            domain = host_val or domain
    except Exception:
        domain = None
    dt = _parse_time(ts)
    bucket = None
    try:
        if dt:
            bucket = dt.strftime('%Y-%m-%d %H:%M')
        elif isinstance(ts, str) and len(ts) >= 16:
            bucket = ts[:16]
    except Exception:
        bucket = 'unknown'
    # Debug logging (limited)
    try:
        print(f"[modsec] _parse_modsec_event ts_raw={ts} parsed={dt.isoformat() if dt else None} bucket={bucket}")
    except Exception:
        pass
    return {
        'timestamp': ts,
        'bucket': bucket or 'unknown',
        'datetime': dt.isoformat() if dt else None,
        'src_ip': ip,
        'rule_ids': rule_ids,
        'severity': severity,
        'method': method,
        'uri': uri,
        'domain': domain,
        'status': status,
    }

def get_modsec_events(limit=1000, since=None, until=None, ip=None, rule_id=None):
    lines = read_modsec_audit_lines(max_lines=max(1000, limit * 10))
    events = []
    for ln in lines:
        ln = ln.strip()
        if not ln:
            continue
        try:
            obj = json.loads(ln)
        except Exception:
            continue
        ev = _parse_modsec_event(obj)
        events.append(ev)
    # Filter
    def in_range(ev):
        dt = None
        if ev.get('datetime'):
            try:
                dt = datetime.fromisoformat(ev['datetime'])
            except Exception:
                dt = None
        ok = True
        if since:
            try:
                since_dt = datetime.fromisoformat(since)
                ok = ok and (dt is None or dt >= since_dt)
            except Exception:
                pass
        if until:
            try:
                until_dt = datetime.fromisoformat(until)
                ok = ok and (dt is None or dt <= until_dt)
            except Exception:
                pass
        if ip:
            ok = ok and (ev.get('src_ip') == ip)
        if rule_id:
            ok = ok and (rule_id in (ev.get('rule_ids') or []))
        return ok
    events = [e for e in events if in_range(e)]
    events = events[-limit:] if limit and len(events) > limit else events
    return events

def aggregate_modsec_stats(events):
    series = {}
    rule_counts = {}
    ip_counts = {}
    total = len(events)
    today = week = month = 0
    now = datetime.now().astimezone()  # use server local timezone
    debug_samples = 0
    for ev in events:
        b = ev.get('bucket', 'unknown')
        series[b] = series.get(b, 0) + 1
        for rid in (ev.get('rule_ids') or []):
            rule_counts[rid] = rule_counts.get(rid, 0) + 1
        ip = ev.get('src_ip')
        if ip:
            ip_counts[ip] = ip_counts.get(ip, 0) + 1
        # totals by time ranges
        try:
            dt_str = ev.get('datetime') or ev.get('timestamp') or None
            dt = _parse_time(dt_str) if dt_str else None
            if dt:
                dt_local = dt.astimezone(now.tzinfo)
                if debug_samples < 5:
                    try:
                        diff_days = (now - dt_local).days
                        print(f"[modsec] dt_str={dt_str} parsed_utc={dt.isoformat()} parsed_local={dt_local.isoformat()} diff_days={diff_days}", flush=True)
                        debug_samples += 1
                    except Exception:
                        pass
                if dt_local.date() == now.date():
                    today += 1
                if (now - dt_local) <= timedelta(days=7):
                    week += 1
                if dt_local.year == now.year and dt_local.month == now.month:
                    month += 1
        except Exception as e:
            try:
                if debug_samples < 5:
                    print(f"[modsec] aggregate error: {e}", flush=True)
                    debug_samples += 1
            except Exception:
                pass
            pass
    series_list = sorted([{'bucket': k, 'count': v} for k, v in series.items()], key=lambda x: x['bucket'])
    top_rules = sorted([{'rule_id': k, 'count': v} for k, v in rule_counts.items()], key=lambda x: x['count'], reverse=True)[:10]
    top_ips = sorted([{'ip': k, 'count': v} for k, v in ip_counts.items()], key=lambda x: x['count'], reverse=True)[:10]
    unique_ips = len(ip_counts)
    return {
        'total': total,
        'unique_ips': unique_ips,
        'today': today,
        'week': week,
        'month': month,
        'series': series_list,
        'top_rules': top_rules,
        'top_ips': top_ips,
    }

@app.route('/api/modsecurity/stats')
@login_required
def api_modsec_stats():
    limit = int(request.args.get('limit', '2000') or '2000')
    events = get_modsec_events(limit=limit)
    stats = aggregate_modsec_stats(events)
    # Hitung jumlah IP terblokir dari blocked_ips.conf
    try:
        blocked_ips_file = os.path.join(os.path.dirname(NGINX_CONF_DIR), 'blocked_ips.conf')
        blocked_ips_count = 0
        if os.path.exists(blocked_ips_file):
            with open(blocked_ips_file, 'r') as f:
                for line in f:
                    line = line.strip()
                    if line.startswith('deny ') and line.endswith(';'):
                        blocked_ips_count += 1
        stats['blocked_ips_count'] = blocked_ips_count
    except Exception:
        stats['blocked_ips_count'] = 0
    return jsonify({'success': True, **stats})

@app.route('/api/modsecurity/attacks')
@login_required
def api_modsec_attacks():
    limit = int(request.args.get('limit', '500') or '500')
    since = request.args.get('since')
    until = request.args.get('until')
    ip = request.args.get('ip')
    rule_id = request.args.get('rule_id')
    events = get_modsec_events(limit=limit, since=since, until=until, ip=ip, rule_id=rule_id)
    return jsonify({'success': True, 'events': events, 'count': len(events)})

@app.route('/api/modsecurity/top-rules')
@login_required
def api_modsec_top_rules():
    limit = int(request.args.get('limit', '2000') or '2000')
    events = get_modsec_events(limit=limit)
    stats = aggregate_modsec_stats(events)
    return jsonify({'success': True, 'top_rules': stats['top_rules']})

@app.route('/api/modsecurity/top-ips')
@login_required
def api_modsec_top_ips():
    limit = int(request.args.get('limit', '2000') or '2000')
    events = get_modsec_events(limit=limit)
    stats = aggregate_modsec_stats(events)
    return jsonify({'success': True, 'top_ips': stats['top_ips']})

@app.route('/modsecurity-dashboard')
@login_required
def modsecurity_dashboard_page():
    nginx_status = check_nginx_status()
    return render_template('modsecurity_dashboard.html', nginx_status=nginx_status)

@app.route('/api/modsecurity-logs/<domain>')
@login_required
def api_modsec_logs_by_domain(domain):
    """Get ModSecurity logs filtered by domain with date range support"""
    try:
        if not is_valid_site_name(domain):
            return jsonify({'success': False, 'error': 'Invalid domain name'}), 400

        # Get date range parameters
        start_date = request.args.get('start_date')
        end_date = request.args.get('end_date')

        # Convert date strings to datetime objects
        since = None
        until = None

        if start_date:
            try:
                since = datetime.fromisoformat(start_date).replace(tzinfo=timezone.utc)
            except ValueError:
                return jsonify({'success': False, 'error': 'Invalid start_date format'}), 400

        if end_date:
            try:
                # Add one day to include the entire end date
                until = datetime.fromisoformat(end_date).replace(tzinfo=timezone.utc) + timedelta(days=1)
            except ValueError:
                return jsonify({'success': False, 'error': 'Invalid end_date format'}), 400

        # Get events with domain filtering
        events = get_modsec_events_by_domain(domain, since=since, until=until)

        # Format logs for frontend
        logs = []
        for event in events:
            # Get the most relevant rule ID and message
            rule_ids = event.get('rule_ids', [])
            rule_id = rule_ids[0] if rule_ids else None

            # Extract message from event or use default
            message = 'ModSecurity rule triggered'
            if event.get('severity'):
                message = f"{event['severity']} severity rule triggered"

            logs.append({
                'time': event.get('bucket', 'unknown'),
                'ip': event.get('src_ip', 'unknown'),
                'method': event.get('method', 'unknown'),
                'uri': event.get('uri', 'unknown'),
                'rule_id': rule_id,
                'severity': event.get('severity', 'unknown'),
                'action': 'blocked' if event.get('status') and str(event.get('status')).startswith('4') else 'detected',
                'message': message,
                'domain': event.get('domain', domain)
            })

        return jsonify({
            'success': True,
            'domain': domain,
            'logs': logs,
            'count': len(logs),
            'date_range': {
                'start_date': start_date,
                'end_date': end_date
            }
        })
    except Exception as e:
        logger.exception("Error in api_modsec_logs_by_domain")
        return jsonify({'success': False, 'error': 'Failed to fetch ModSecurity logs', 'details': str(e)}), 500

@app.route('/api/block-ip', methods=['POST'])
@login_required
def api_block_ip():
    """Block an IP address using nginx configuration"""
    try:
        data = request.get_json()
        if not data or 'ip' not in data:
            return jsonify({'success': False, 'error': 'IP address is required'}), 400
        
        ip = data['ip'].strip()
        
        # Validate IP address format
        import ipaddress
        try:
            ipaddress.ip_address(ip)
        except ValueError:
            return jsonify({'success': False, 'error': 'Invalid IP address format'}), 400
        
        # Path to blocked IPs file
        blocked_ips_file = os.path.join(os.path.dirname(NGINX_CONF_DIR), 'blocked_ips.conf')
        
        # Read existing blocked IPs
        blocked_ips = set()
        if os.path.exists(blocked_ips_file):
            with open(blocked_ips_file, 'r') as f:
                for line in f:
                    line = line.strip()
                    if line.startswith('deny ') and line.endswith(';'):
                        blocked_ip = line[5:-1].strip()
                        blocked_ips.add(blocked_ip)
        
        # Check if IP is already blocked
        if ip in blocked_ips:
            return jsonify({'success': True, 'message': f'IP {ip} is already blocked'})
        
        # Add IP to blocked list
        blocked_ips.add(ip)
        
        # Write updated blocked IPs file
        with open(blocked_ips_file, 'w') as f:
            f.write('# Blocked IPs for ModSecurity\n')
            for blocked_ip in sorted(blocked_ips):
                f.write(f'deny {blocked_ip};\n')
        
        # Ensure server configs include blocked_ips.conf
        update_server_configs_with_blocked_ips()
        
        # Reload nginx configuration
        reload_result = subprocess.run(['docker', 'exec', 'mlite_nginx', 'nginx', '-s', 'reload'], 
                                     capture_output=True, text=True, timeout=30)
        
        if reload_result.returncode != 0:
            return jsonify({'success': False, 'error': f'Failed to reload nginx: {reload_result.stderr}'}), 500
        
        return jsonify({'success': True, 'message': f'IP {ip} has been blocked successfully'})
        
    except Exception as e:
        logger.error(f"Error blocking IP: {e}")
        return jsonify({'success': False, 'error': str(e)}), 500

@app.route('/api/unblock-ip', methods=['POST'])
@login_required
def api_unblock_ip():
    """Unblock an IP address from nginx configuration"""
    try:
        data = request.get_json()
        if not data or 'ip' not in data:
            return jsonify({'success': False, 'error': 'IP address is required'}), 400
        
        ip = data['ip'].strip()
        
        # Validate IP address format
        import ipaddress
        try:
            ipaddress.ip_address(ip)
        except ValueError:
            return jsonify({'success': False, 'error': 'Invalid IP address format'}), 400
        
        # Path to blocked IPs file
        blocked_ips_file = os.path.join(os.path.dirname(NGINX_CONF_DIR), 'blocked_ips.conf')
        
        # Read existing blocked IPs
        blocked_ips = set()
        if os.path.exists(blocked_ips_file):
            with open(blocked_ips_file, 'r') as f:
                for line in f:
                    line = line.strip()
                    if line.startswith('deny ') and line.endswith(';'):
                        blocked_ip = line[5:-1].strip()
                        blocked_ips.add(blocked_ip)
        
        # Check if IP is blocked
        if ip not in blocked_ips:
            return jsonify({'success': True, 'message': f'IP {ip} is not blocked'})
        
        # Remove IP from blocked list
        blocked_ips.discard(ip)
        
        # Write updated blocked IPs file
        with open(blocked_ips_file, 'w') as f:
            f.write('# Blocked IPs for ModSecurity\n')
            for blocked_ip in sorted(blocked_ips):
                f.write(f'deny {blocked_ip};\n')
        
        # Ensure server configs include blocked_ips.conf
        update_server_configs_with_blocked_ips()
        
        # Reload nginx configuration
        reload_result = subprocess.run(['docker', 'exec', 'mlite_nginx', 'nginx', '-s', 'reload'], 
                                     capture_output=True, text=True, timeout=30)
        
        if reload_result.returncode != 0:
            return jsonify({'success': False, 'error': f'Failed to reload nginx: {reload_result.stderr}'}), 500
        
        return jsonify({'success': True, 'message': f'IP {ip} has been unblocked successfully'})
        
    except Exception as e:
        logger.error(f"Error unblocking IP: {e}")
        return jsonify({'success': False, 'error': str(e)}), 500

@app.route('/api/blocked-ips', methods=['GET'])
@login_required
def api_blocked_ips():
    """Get list of blocked IP addresses"""
    try:
        # Path to blocked IPs file
        blocked_ips_file = os.path.join(os.path.dirname(NGINX_CONF_DIR), 'blocked_ips.conf')
        
        # Read existing blocked IPs
        blocked_ips = []
        if os.path.exists(blocked_ips_file):
            with open(blocked_ips_file, 'r') as f:
                for line in f:
                    line = line.strip()
                    if line.startswith('deny ') and line.endswith(';'):
                        blocked_ip = line[5:-1].strip()
                        blocked_ips.append(blocked_ip)
        
        return jsonify({'success': True, 'blocked_ips': sorted(blocked_ips)})
        
    except Exception as e:
        logger.error(f"Error getting blocked IPs: {e}")
        return jsonify({'success': False, 'error': str(e)}), 500

def get_modsec_events_by_domain(domain, limit=1000, since=None, until=None):
    """Get ModSecurity events filtered by specific domain"""
    # Validate domain input to avoid NoneType errors
    if not isinstance(domain, str) or not domain.strip():
        logger.warning(f"get_modsec_events_by_domain called with invalid domain: {domain!r}")
        return []
    target_domain = domain.strip().lower()

    lines = read_modsec_audit_lines(max_lines=max(1000, limit * 10))
    events = []
    
    for ln in lines:
        ln = ln.strip()
        if not ln:
            continue
        try:
            obj = json.loads(ln)
        except Exception:
            continue
        
        ev = _parse_modsec_event(obj)
        if not isinstance(ev, dict):
            continue
        
        # Filter by domain - case insensitive comparison with null-safety
        event_domain = (ev.get('domain') or '').lower()
        
        # Check if this event belongs to the target domain
        if not event_domain or target_domain not in event_domain:
            continue
        
        # Apply date range filter
        def in_range(ev):
            dt = None
            if ev.get('datetime'):
                try:
                    dt = datetime.fromisoformat(ev['datetime'])
                except Exception:
                    dt = None
            ok = True
            if since:
                ok = ok and (dt is None or dt >= since)
            if until:
                ok = ok and (dt is None or dt <= until)
            return ok
        
        if in_range(ev):
            events.append(ev)
    
    # Limit results and return most recent first
    events = events[-limit:] if limit and len(events) > limit else events
    return events

def ensure_blocked_ips_config():
    """Ensure blocked IPs configuration is included in nginx main config"""
    try:
        # Path to main nginx config
        main_config_path = os.path.join(os.path.dirname(NGINX_CONF_DIR), 'nginx.conf')
        include_line = f"    include /etc/nginx/blocked_ips.conf;"
        
        # Check if main config exists and read it
        if os.path.exists(main_config_path):
            with open(main_config_path, 'r') as f:
                config_content = f.read()
            
            # Check if blocked_ips.conf is already included
            if "include /etc/nginx/blocked_ips.conf" in config_content:
                return True  # Already included
            
            # Add include directive to http block
            if 'http {' in config_content:
                # Find the http block and add include directive
                lines = config_content.split('\n')
                new_lines = []
                in_http_block = False
                http_brace_count = 0
                
                for line in lines:
                    new_lines.append(line)
                    
                    if 'http {' in line:
                        in_http_block = True
                        http_brace_count = 1
                    elif in_http_block:
                        if '{' in line:
                            http_brace_count += line.count('{')
                        if '}' in line:
                            http_brace_count -= line.count('}')
                            
                        # Add include directive before the closing brace of http block
                        if http_brace_count == 1 and line.strip() == '}':
                            # Remove the closing brace we just added
                            new_lines.pop()
                            # Add our include directive
                            new_lines.append(include_line)
                            # Add the closing brace back
                            new_lines.append(line)
                            in_http_block = False
                
                # Write updated config
                with open(main_config_path, 'w') as f:
                    f.write('\n'.join(new_lines))
                
                return True
        
        # If main config doesn't exist or we couldn't modify it, 
        # we'll rely on per-server include directives
        return False
        
    except Exception as e:
        logger.error(f"Error ensuring blocked IPs config: {e}")
        return False

def update_server_configs_with_blocked_ips():
    """Update all server configurations to include blocked_ips.conf"""
    try:
        blocked_ips_file = os.path.join(os.path.dirname(NGINX_CONF_DIR), 'blocked_ips.conf')
        include_directive = f"    include /etc/nginx/blocked_ips.conf;"
        
        # Process all .conf files in nginx conf directory
        for filename in os.listdir(NGINX_CONF_DIR):
            if filename.endswith('.conf') and filename != 'blocked_ips.conf':
                config_path = os.path.join(NGINX_CONF_DIR, filename)
                
                try:
                    with open(config_path, 'r') as f:
                        content = f.read()
                    
                    # Update or add blocked_ips include
                    if 'include /etc/nginx/conf.d/blocked_ips.conf' in content:
                        content = content.replace('/etc/nginx/conf.d/blocked_ips.conf', '/etc/nginx/blocked_ips.conf')
                        with open(config_path, 'w') as f:
                            f.write(content)
                        logger.info(f"Replaced old include path in {filename}")
                        continue
                    
                    if 'include /etc/nginx/blocked_ips.conf' in content:
                        continue
                    
                    # Add include directive to server block
                    if 'server {' in content:
                        lines = content.split('\n')
                        new_lines = []
                        in_server_block = False
                        server_brace_count = 0
                        include_added = False
                        
                        for line in lines:
                            new_lines.append(line)
                            
                            if 'server {' in line and not include_added:
                                in_server_block = True
                                server_brace_count = 1
                            elif in_server_block and not include_added:
                                if '{' in line:
                                    server_brace_count += line.count('{')
                                if '}' in line:
                                    server_brace_count -= line.count('}')
                                
                                # Add include directive after the opening brace of server block
                                if server_brace_count >= 1 and line.strip() == '':
                                    # Find a good place to add the include (after server_name or listen)
                                    prev_lines = [l.strip() for l in new_lines[-5:] if l.strip()]
                                    if any('server_name' in l or 'listen' in l for l in prev_lines):
                                        new_lines.append(include_directive)
                                        include_added = True
                        
                        # Write updated config
                        with open(config_path, 'w') as f:
                            f.write('\n'.join(new_lines))
                        
                        logger.info(f"Updated {filename} with blocked_ips include")
                
                except Exception as e:
                    logger.error(f"Error updating {filename}: {e}")
                    continue
        
        return True
        
    except Exception as e:
        logger.error(f"Error updating server configs: {e}")
        return False

if __name__ == '__main__':
    # Ensure nginx conf directory exists
    os.makedirs(NGINX_CONF_DIR, exist_ok=True)
    
    # Ensure blocked IPs configuration is included in nginx
    ensure_blocked_ips_config()
    
    port = int(os.environ.get('PORT') or os.environ.get('FLASK_RUN_PORT') or 5000)
    app.run(host='0.0.0.0', port=port, debug=True)
