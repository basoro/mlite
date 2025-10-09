#!/bin/bash
# mLITE Docker Entrypoint Script
# Initializes mLITE application environment for PHP 8.3+

set -e

echo "🔧 Initializing mLITE environment..."

# Ensure correct working directory
cd /var/www/public || exit 1

# --- Folder setup ---
for dir in uploads tmp backups admin/tmp; do
  mkdir -p "/var/www/public/${dir}"
  chmod -R 777 "/var/www/public/${dir}"
done

# --- Update database config ---
if [ -f /var/www/public/config.php ]; then
  echo "⚙️ Updating config.php database host to 'mysql'..."
  sed -i 's/localhost/mysql/g' /var/www/public/config.php
fi

# --- Composer install (only if vendor folder missing) ---
if [ ! -d /var/www/public/vendor ]; then
  echo "📦 Installing Composer dependencies..."
  composer install --no-dev --optimize-autoloader
else
  echo "✅ Composer dependencies already installed, skipping."
fi

# --- Fix mPDF temp directory permissions (if exists) ---
if [ -d /var/www/public/vendor/mpdf/mpdf/tmp ]; then
  chmod -R 777 /var/www/public/vendor/mpdf/mpdf/tmp
fi

echo "🚀 Starting PHP-FPM..."
exec php-fpm
