FROM php:8.1-fpm-alpine

RUN apk update 
RUN apk upgrade 
RUN apk add ca-certificates wget 
RUN update-ca-certificates
RUN apk add libpng-dev

RUN apk add --no-cache mysql-client msmtp perl wget procps shadow libzip libpng libjpeg-turbo libwebp freetype icu

# Install dcmtk from edge testing repository for mini_pacs support
RUN apk add --no-cache dcmtk --repository=http://dl-cdn.alpinelinux.org/alpine/edge/testing/


RUN apk add --no-cache --virtual build-essentials \
    icu-dev icu-libs zlib-dev g++ make automake autoconf libzip-dev \
    libpng-dev libwebp-dev libjpeg-turbo-dev freetype-dev oniguruma-dev linux-headers && \
    docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install gd && \
    docker-php-ext-install mysqli && \
    docker-php-ext-install pdo_mysql && \
    apk add --no-cache sqlite-dev && \
    docker-php-ext-install pdo_sqlite && \
    docker-php-ext-install intl && \
    docker-php-ext-install opcache && \
    docker-php-ext-install exif && \
    docker-php-ext-install zip && \
    docker-php-ext-install bcmath && \
    docker-php-ext-install mbstring && \
    docker-php-ext-install pcntl && \
    apk del build-essentials && rm -rf /usr/src/php*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html
 
