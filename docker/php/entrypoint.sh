#!/bin/bash
set -e
mkdir -p /var/www/public/uploads
chmod -R 777 /var/www/public/uploads

mkdir -p /var/www/public/tmp
chmod -R 777 /var/www/public/tmp

mkdir -p /var/www/public/backups &&
chmod -R 777 /var/www/public/backups &&

mkdir -p /var/www/public/admin/tmp
chmod -R 777 /var/www/public/admin/tmp

sed -i 's/localhost/mysql/g' /var/www/public/config.php

composer install --no-dev --optimize-autoloader

chmod -R 777 /var/www/public/vendor/mpdf/mpdf/tmp

exec php-fpm