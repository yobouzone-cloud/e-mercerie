FROM webdevops/php-nginx:8.3

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN chown -R application:application /app/storage /app/bootstrap/cache
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Expose web port
EXPOSE 80

# Démarrer le serveur nginx + php-fpm
CMD ["supervisord", "-n"]
