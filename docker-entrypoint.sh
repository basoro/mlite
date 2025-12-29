#!/bin/sh
set -e

# Fix permissions for backups and uploads directories
# This is critical when using persistent volumes in Railway/Docker
# as they might be mounted with root ownership by default.
echo "Fixing permissions for persistent directories..."

for dir in backups uploads tmp admin/tmp; do
    if [ -d "/var/www/html/$dir" ]; then
        chown -R www-data:www-data "/var/www/html/$dir"
        chmod -R 775 "/var/www/html/$dir"
    fi
done

# Execute the CMD (supervisord)
exec "$@"
