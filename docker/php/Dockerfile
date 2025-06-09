FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    git \ 
    curl \
    libpng-dev \ 
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \ 
    libzip-dev \
    libicu-dev \ 
    libfreetype6-dev \
    zip \
    unzip

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install \
    pdo_mysql \ 
    mysqli \  
    mbstring \ 
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip \
    opcache

RUN docker-php-ext-enable mysqli

RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN docker-php-ext-install -j$(nproc) gd 

RUN pecl install redis && docker-php-ext-enable redis

RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY ./php/php.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/public

COPY ./php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]

EXPOSE 9000
