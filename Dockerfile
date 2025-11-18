# ----------------------------
# Image PHP + Nginx optimisée
# ----------------------------
FROM webdevops/php-nginx:8.3

# Dossier de travail
WORKDIR /app

# Copier les fichiers du projet
COPY . .

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Donner les permissions à Laravel
RUN chown -R application:application /app/storage /app/bootstrap/cache

# Optimisations Laravel
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Exposer le port Nginx
EXPOSE 80

# Commande de démarrage
CMD php artisan migrate --force && supervisord
