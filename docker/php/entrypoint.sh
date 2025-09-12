#!/bin/bash
# mLITE Docker Entrypoint Script
# Initializes mLITE application environment for PHP 8.3
set -e

# Create and set permissions for mLITE upload directories
mkdir -p /var/www/public/uploads
chmod -R 777 /var/www/public/uploads

# Create temporary directory for mLITE operations
mkdir -p /var/www/public/tmp
chmod -R 777 /var/www/public/tmp

# Create backup directory for mLITE data
mkdir -p /var/www/public/backups &&
chmod -R 777 /var/www/public/backups &&

# Create admin temporary directory
mkdir -p /var/www/public/admin/tmp
chmod -R 777 /var/www/public/admin/tmp

# Configure database connection for Docker environment
sed -i 's/localhost/mysql/g' /var/www/public/config.php

# Install PHP dependencies optimized for production
composer install --no-dev --optimize-autoloader

# Set permissions for mPDF temporary directory
chmod -R 777 /var/www/public/vendor/mpdf/mpdf/tmp

# Start PHP-FPM for mLITE application
exec php-fpm