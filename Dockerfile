FROM dunglas/frankenphp:php8.2-bookworm

# Install system dependencies for GD and other extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files and install dependencies
COPY composer.json ./
RUN composer install --optimize-autoloader --no-scripts --no-interaction --no-dev

# Copy application code
COPY . .

# Run composer scripts after code is copied
RUN composer dump-autoload --optimize

# Expose port
EXPOSE 80

# FrankenPHP serves from /app by default
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]

