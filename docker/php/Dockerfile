FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    git \ 
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \ 
    libzip-dev \
    libicu-dev \
    zip \
    unzip

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install \
    pdo_mysql \ 
    mbstring \ 
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip \
    opcache

RUN pecl install redis && docker-php-ext-enable redis

RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY ./php/php.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/public

CMD ["php-fpm"]

EXPOSE 9000