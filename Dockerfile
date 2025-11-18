# Étape 1 : Utiliser une image PHP avec les extensions nécessaires
FROM php:8.3-fpm


# Installer les dépendances système et Node.js
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    zip \
    curl \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail
WORKDIR /var/www/html

# Copier le code Laravel
COPY . .


# Installer les dépendances PHP
RUN composer install --optimize-autoloader

# Installer les dépendances front et compiler les assets
RUN npm install && npm run build

# Donner les bonnes permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Générer la clé de l'application
# RUN php artisan key:generate

# Exposer le port
EXPOSE 8000

# Démarrage de Laravel
CMD php artisan migrate:fresh --seed --force && php artisan serve --host=0.0.0.0 --port=8000
