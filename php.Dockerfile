# FROM php:fpm-alpine
FROM guhkun13/php-for-mlite:latest

# RUN apk update 
#     && apk upgrade \
#     && apk add ca-certificates wget \
#     && update-ca-certificates

# tidak perlu install mysqli dan pdo karena sudah diinstall di image di atas!!!
# soalnya lumayan besar size yg di-install. huft
# RUN docker-php-ext-install mysqli && \
#     docker-php-ext-install pdo_mysql

WORKDIR /var/www/html

# COPY . .

# override config.php using docker-config

# COPY config-docker.php config.php

# RUN composer install

# RUN mkdir -p uploads 
# RUN mkdir -p tmp 
# RUN mkdir -p admin/tmp

# RUN chmod -R 777 .

# RUN composer install