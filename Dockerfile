# Utilise l'image de base PHP 8.2 en mode CLI
FROM php:8.2-apache

# Définit le répertoire de travail par défaut à /var/www/html
WORKDIR /var/www/calDAV

# Met à jour les paquets et installe les dépendances nécessaires sans les recommandations supplémentaires
RUN apt-get update && apt-get install -y --no-install-recommends \
    locales apt-utils git libicu-dev g++ libpng-dev \
    libxml2-dev libzip-dev libonig-dev libxslt-dev unzip libpq-dev nodejs npm wget \
    apt-transport-https lsb-release ca-certificates librdkafka-dev

# Configure les locales pour l'anglais et le français
RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen \
    && echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen \
    && locale-gen

# Télécharge et installe Composer, un gestionnaire de dépendances pour PHP
RUN curl -sS https://getcomposer.org/installer | php -- \
    && mv composer.phar /usr/local/bin/composer

# Installe l'extension rdkafka
RUN pecl install rdkafka && docker-php-ext-enable rdkafka

# Définir la variable d'environnement pour permettre l'exécution des plugins Composer en tant que super utilisateur
ENV COMPOSER_ALLOW_SUPERUSER=1

# Installer le plugin enqueue/rdkafka
RUN composer require enqueue/rdkafka

# Télécharge et installe l'outil en ligne de commande Symfony
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin

# Configure et installe plusieurs extensions PHP nécessaires
RUN docker-php-ext-configure intl \
    && docker-php-ext-install pdo pdo_mysql \
    pdo_pgsql opcache intl zip calendar dom mbstring gd xsl \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Installe et active l'extension APCu pour le cache utilisateur
RUN pecl install apcu && docker-php-ext-enable apcu

# Copie les fichiers du projet dans le conteneur
# COPY . ./calDAV
COPY . .

# Run Composer install, ignoring the missing extension requirement temporarily
RUN composer install --no-dev --no-interaction --no-scripts

# Configure les informations utilisateur pour Git
RUN git config --global user.email "you@example.com" \
    && git config --global user.name "Your Name"

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Crée une configuration Apache pour pointer vers le dossier public
RUN echo "<VirtualHost *:80>\n\
    DocumentRoot /var/www/calDAV/public\n\
    <Directory /var/www/calDAV/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>" > /etc/apache2/sites-available/000-default.conf

# Set the appropriate permissions and user
RUN chown -R www-data:www-data /var/www/calDAV

# Change to www-data user
USER www-data

# Expose port 80
EXPOSE 80

# Démarre Apache dans le conteneur
CMD ["apache2-foreground"]