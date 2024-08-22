# syntax=docker/dockerfile:1.4

# Stage 1: Composer installation
FROM composer:lts as deps
WORKDIR /var/www/html/calDAV

# Copy composer.json and composer.lock before running composer install
COPY composer.json composer.lock ./

# Install symfony/runtime
# RUN composer require symfony/runtime

# Run Composer install, ignoring the missing extension requirement temporarily
# RUN --mount=type=cache,target=/root/.composer/cache \
    # composer install --no-dev --no-interaction --no-scripts --ignore-platform-req=ext-rdkafka

# Stage 2: PHP 8.1 with Apache, Kafka extension, and URL rewrite enabled
FROM php:8.1-apache as final

# Set working directory
WORKDIR /var/www/html/calDAV

# Copy Composer from the deps stage
COPY --from=deps /usr/bin/composer /usr/bin/composer

# Install necessary PHP extensions
RUN apt-get update && apt-get install -y libzstd-dev liblz4-dev && \
    docker-php-ext-install pdo pdo_mysql && \
    mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy Composer dependencies and application source code
# COPY --from=deps /var/www/html/calDAV/vendor/ ./vendor
COPY . .

# Set the appropriate permissions and user
# RUN chown -R www-data:www-data /var/www/html/calDAV

# Change to www-data user
# USER www-data

# Run Composer scripts now that all files are in place
# RUN composer dump-autoload --optimize && php bin/console cache:clear

# Expose port 80
EXPOSE 80

# Optionally, you can add a HEALTHCHECK here
# HEALTHCHECK CMD curl --fail http://localhost/ || exit 1
