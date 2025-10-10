import os
import subprocess
import requests
import shlex
import re
from datetime import datetime
from flask import Flask, render_template, request, redirect, url_for, flash, jsonify

app = Flask(__name__)
app.secret_key = 'nginx-manager-secret-key'

# Configuration
NGINX_CONF_DIR = '/etc/nginx/conf.d'
NGINX_CONTAINER_NAME = os.environ.get('NGINX_CONTAINER_NAME', 'mlite_nginx')
PHP_CONTAINER_NAME = os.environ.get('PHP_CONTAINER_NAME', 'mlite_php')
DNS_HOSTS_FILE = '/app/dns/dnsmasq.hosts'
DNS_CONTAINER_NAME = 'mlite_dns'
DNS_SERVER_IP = '10.20.0.10'
# Base web root inside containers (mounted from host ../)
PHP_WEBROOT_BASE = '/var/www/public'
# Optional toggle to create default index.html after directory creation (env CREATE_DEFAULT_INDEX=1/0)
CREATE_DEFAULT_INDEX = os.environ.get('CREATE_DEFAULT_INDEX', '1') == '1'

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
                            if 'fastcgi_pass php:9000' in content:
                                site_type = 'php'
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
                    except Exception as e:
                        print(f"Error reading config file {filename}: {e}")
                    
                    sites.append({
                        'domain': domain,
                        'type': site_type or 'proxy',
                        'port': port,
                        'root': root_dir,
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
        for container in (NGINX_CONTAINER_NAME, PHP_CONTAINER_NAME):
            try:
                cmd = ['docker', 'exec', container, 'sh', '-c', f"cat > {quoted_index} << 'EOF'\n{content}\nEOF\nchmod 644 {quoted_index}"]
                res = subprocess.run(cmd, capture_output=True, text=True)
                if res.returncode == 0:
                    return True, 'Default index.html created'
                last_err = res.stderr.strip() or res.stdout.strip()
            except Exception as e:
                last_err = str(e)
        return False, f'Failed to create index.html: {last_err or "unknown error"}'
    except Exception as e:
        return False, f'Failed to prepare index content: {str(e)}'


def create_nginx_config(domain, site_type='proxy', port=None, root_dir='/var/www/public'):
    """Create nginx configuration file for either reverse proxy or PHP-FPM"""
    if site_type == 'php':
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
        fastcgi_pass php:9000;
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

def add_dns_record(domain, ip_address='127.0.0.1'):
    """Add DNS record to dnsmasq hosts file"""
    try:
        # Ensure directory exists
        os.makedirs(os.path.dirname(DNS_HOSTS_FILE), exist_ok=True)
        
        # Read existing records
        existing_records = []
        if os.path.exists(DNS_HOSTS_FILE):
            with open(DNS_HOSTS_FILE, 'r') as f:
                existing_records = f.readlines()
        
        # Check if domain already exists
        for record in existing_records:
            if domain in record:
                return True, "Domain already exists in DNS"
        
        # Add new record
        with open(DNS_HOSTS_FILE, 'a') as f:
            f.write(f"{ip_address} {domain}\n")
        
        # Reload dnsmasq
        return reload_dnsmasq()
        
    except Exception as e:
        return False, f"Error adding DNS record: {str(e)}"

def remove_dns_record(domain):
    """Remove DNS record from dnsmasq hosts file"""
    try:
        if not os.path.exists(DNS_HOSTS_FILE):
            return True, "No DNS records file found"
        
        # Read existing records
        with open(DNS_HOSTS_FILE, 'r') as f:
            existing_records = f.readlines()
        
        # Filter out records containing the domain
        filtered_records = [record for record in existing_records if domain not in record]
        
        # Write back filtered records
        with open(DNS_HOSTS_FILE, 'w') as f:
            f.writelines(filtered_records)
        
        # Reload dnsmasq
        return reload_dnsmasq()
        
    except Exception as e:
        return False, f"Error removing DNS record: {str(e)}"

def reload_dnsmasq():
    """Reload dnsmasq service"""
    try:
        # Send SIGHUP to dnsmasq to reload configuration
        result = subprocess.run([
            'docker', 'exec', DNS_CONTAINER_NAME, 'killall', '-HUP', 'dnsmasq'
        ], capture_output=True, text=True)
        
        if result.returncode == 0:
            return True, "DNS reloaded successfully"
        else:
            return False, f"DNS reload failed: {result.stderr}"
    except Exception as e:
        return False, f"Error reloading DNS: {str(e)}"

def check_dns_status():
    """Check if DNS container is running"""
    try:
        result = subprocess.run(
            ['docker', 'inspect', '-f', '{{.State.Running}}', DNS_CONTAINER_NAME],
            capture_output=True, text=True
        )
        if result.returncode == 0:
            return result.stdout.strip().lower() == 'true'
    except Exception:
        pass
    return False

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

@app.route('/')
def dashboard():
    """Dashboard page showing all sites - HOT RELOAD TEST"""
    sites = get_sites()
    nginx_status = check_nginx_status()
    dns_status = check_dns_status()
    return render_template('dashboard.html', sites=sites, nginx_status=nginx_status, dns_status=dns_status)

@app.route('/add-site', methods=['GET', 'POST'])
def add_site():
    nginx_status = check_nginx_status()
    dns_status = check_dns_status()
    """Add new site page"""
    if request.method == 'POST':
        domain = request.form.get('domain', '').strip()
        site_type = request.form.get('site_type', 'proxy').strip()
        port = request.form.get('port', '').strip()
        root_dir = request.form.get('root_dir', '/var/www/public').strip()
        
        # Validation
        if not domain:
            flash('Domain is required', 'error')
            return render_template('add_site.html', nginx_status=nginx_status, dns_status=dns_status)
        
        if site_type == 'proxy':
            if not port:
                flash('Port is required for proxy sites', 'error')
                return render_template('add_site.html', nginx_status=nginx_status, dns_status=dns_status)
            try:
                port_int = int(port)
                if port_int < 1 or port_int > 65535:
                    flash('Port must be between 1 and 65535', 'error')
                    return render_template('add_site.html', nginx_status=nginx_status, dns_status=dns_status)
            except ValueError:
                flash('Port must be a valid number', 'error')
                return render_template('add_site.html', nginx_status=nginx_status, dns_status=dns_status)
        elif site_type == 'php':
            if not root_dir:
                flash('Root directory is required for PHP-FPM sites', 'error')
                return render_template('add_site.html', nginx_status=nginx_status, dns_status=dns_status)
            # Validate and ensure directory within /var/www/public
            try:
                safe_root = safe_join(PHP_WEBROOT_BASE, root_dir)
            except ValueError as ve:
                flash(str(ve), 'error')
                return render_template('add_site.html', nginx_status=nginx_status, dns_status=dns_status)
            ok, msg = ensure_directory(safe_root)
            if not ok:
                flash(msg, 'error')
                return render_template('add_site.html', nginx_status=nginx_status, dns_status=dns_status)
            # Use the safe normalized root in config
            root_dir = safe_root
            # Optionally create default index.html (non-blocking if fails)
            if CREATE_DEFAULT_INDEX:
                ok_idx, msg_idx = create_default_index(root_dir, domain)
                if not ok_idx:
                    flash(f'Peringatan: {msg_idx}', 'warning')
        else:
            flash('Invalid site type', 'error')
            return render_template('add_site.html', nginx_status=nginx_status, dns_status=dns_status)

        # Check if domain already exists
        existing_sites = get_sites()
        if any(site['domain'] == domain for site in existing_sites):
            flash('Domain already exists', 'error')
            return render_template('add_site.html', nginx_status=nginx_status, dns_status=dns_status)
        
        # Create nginx config
        created = False
        if site_type == 'php':
            created = create_nginx_config(domain, site_type='php', root_dir=root_dir)
        else:
            created = create_nginx_config(domain, site_type='proxy', port=port)
        
        if created:
            # Add DNS record
            dns_success, dns_message = add_dns_record(domain)
            if not dns_success:
                print(f"DNS warning: {dns_message}")
            
            # Reload nginx
            success, message = reload_nginx()
            if success:
                flash('Site added successfully', 'success')
                return redirect(url_for('dashboard'))
            else:
                # If reload fails, delete the config file and DNS record
                delete_nginx_config(domain)
                remove_dns_record(domain)
                flash(f'Failed to reload nginx: {message}', 'error')
        else:
            flash('Failed to create configuration file', 'error')
            # Fallthrough to final render
    
    return render_template('add_site.html', nginx_status=nginx_status, dns_status=dns_status)

@app.route('/delete-site/<domain>')
def delete_site(domain):
    """Delete site configuration"""
    if delete_nginx_config(domain):
        # Remove DNS record
        dns_success, dns_message = remove_dns_record(domain)
        if not dns_success:
            print(f"DNS warning: {dns_message}")
        
        success, message = reload_nginx()
        if success:
            flash('Site deleted successfully', 'success')
        else:
            flash(f'Site deleted but nginx reload failed: {message}', 'warning')
    else:
        flash('Failed to delete site configuration', 'error')
    
    return redirect(url_for('dashboard'))

@app.route('/api/sites')
def api_sites():
    """API endpoint to get all sites"""
    sites = get_sites()
    return jsonify({
        'sites': sites,
        'total': len(sites)
    })

@app.route('/api/nginx-status')
def api_nginx_status():
    """API endpoint for nginx status"""
    sites = get_sites()
    nginx_running = check_nginx_status()
    
    return jsonify({
        'status': 'running' if nginx_running else 'stopped',
        'total_sites': len(sites),
        'sites': sites
    })

@app.route('/api/reload-nginx')
def api_reload_nginx():
    """API endpoint to reload nginx"""
    success, message = reload_nginx()
    return jsonify({
        'success': success,
        'message': message
    })

@app.route('/api/dns-status')
def api_dns_status():
    """API endpoint for DNS status"""
    dns_running = check_dns_status()
    
    return jsonify({
        'status': 'running' if dns_running else 'stopped',
        'server_ip': DNS_SERVER_IP
    })

@app.route('/api/dns-records')
def api_dns_records():
    """API endpoint to get DNS records"""
    records = []
    try:
        if os.path.exists(DNS_HOSTS_FILE):
            with open(DNS_HOSTS_FILE, 'r') as f:
                for line in f:
                    line = line.strip()
                    if line and not line.startswith('#'):
                        parts = line.split()
                        if len(parts) >= 2:
                            records.append({
                                'ip': parts[0],
                                'domain': parts[1]
                            })
    except Exception as e:
        return jsonify({'error': str(e)}), 500
    
    return jsonify({
        'records': records,
        'total': len(records)
    })

@app.route('/api/add-dns-record', methods=['POST'])
def api_add_dns_record():
    """API endpoint to add DNS record"""
    data = request.get_json()
    domain = data.get('domain', '').strip()
    ip_address = data.get('ip', '127.0.0.1').strip()
    
    if not domain:
        return jsonify({'error': 'Domain is required'}), 400
    
    success, message = add_dns_record(domain, ip_address)
    return jsonify({
        'success': success,
        'message': message
    })

@app.route('/api/remove-dns-record', methods=['POST'])
def api_remove_dns_record():
    """API endpoint to remove DNS record"""
    data = request.get_json()
    domain = data.get('domain', '').strip()
    
    if not domain:
        return jsonify({'error': 'Domain is required'}), 400
    
    success, message = remove_dns_record(domain)
    return jsonify({
        'success': success,
        'message': message
    })

if __name__ == '__main__':
    # Ensure nginx conf directory exists
    os.makedirs(NGINX_CONF_DIR, exist_ok=True)
    
    app.run(host='0.0.0.0', port=5000, debug=True)# Hot reload test - Fri Oct 10 20:21:00 WITA 2025
# Hot reload test - Fri Oct 10 20:40:45 WITA 2025
