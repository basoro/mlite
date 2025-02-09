FROM php:8.1-fpm-alpine

RUN apk update 
RUN apk upgrade 
RUN apk add ca-certificates wget 
RUN update-ca-certificates
RUN apk add libpng-dev

RUN apk add --no-cache mysql-client msmtp perl wget procps shadow libzip libpng libjpeg-turbo libwebp freetype icu

RUN apk add --no-cache --virtual build-essentials \
    icu-dev icu-libs zlib-dev g++ make automake autoconf libzip-dev \
    libpng-dev libwebp-dev libjpeg-turbo-dev freetype-dev && \
    docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install gd && \
    docker-php-ext-install mysqli && \
    docker-php-ext-install pdo_mysql && \
    docker-php-ext-install intl && \
    docker-php-ext-install opcache && \
    docker-php-ext-install exif && \
    docker-php-ext-install zip && \
    apk del build-essentials && rm -rf /usr/src/php*

WORKDIR /var/www/html    

RUN wget https://getcomposer.org/composer-stable.phar -O /usr/local/bin/composer && chmod +x /usr/local/bin/composer
RUN php /usr/local/bin/composer create-project  basoro/mlite mlite

COPY config.php mlite/config.php

RUN mkdir -p mlite/uploads 
RUN mkdir -p mlite/tmp 
RUN mkdir -p mlite/admin 
RUN mkdir -p mlite/admin/tmp

RUN chmod -R 777 mlite/uploads 
RUN chmod -R 777 mlite/tmp 
RUN chmod -R 777 mlite/admin/tmp 
