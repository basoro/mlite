FROM php:8.1-fpm-alpine
# FROM guhkun13/php-for-mlite:latest

RUN apk update 
RUN apk upgrade 
RUN apk add ca-certificates wget 
RUN update-ca-certificates
RUN apk add libpng-dev

# tidak perlu install mysqli dan pdo karena sudah diinstall di image di atas!!!
# soalnya lumayan besar size yg di-install. huft
RUN docker-php-ext-install mysqli 
RUN docker-php-ext-install pdo_mysql 
RUN docker-php-ext-install gd

WORKDIR /var/www/html

# COPY . .

# override config.php using docker-config
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN php /usr/local/bin/composer create-project  basoro/mlite mlite

COPY config.php mlite/config.php

RUN mkdir -p mlite/uploads 
RUN mkdir -p mlite/tmp 
RUN mkdir -p mlitr/admin/tmp

RUN chmod -R 777 mlite/uploads 
RUN chmod -R 777 mlite/tmp 
RUN chmod -R 777 mlite/admin/tmp 

# RUN composer install