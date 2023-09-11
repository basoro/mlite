FROM php:fpm-alpine

# RUN apk update \
#     && apk upgrade \
#     && apk add ca-certificates wget \
#     && update-ca-certificates

# RUN docker-php-ext-install mysqli && \
#     docker-php-ext-install pdo_mysql

# RUN apk add php-mysqli php-dom php-gd php-mbstring php-pdo php-zip php-curl