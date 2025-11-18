# Base image PHP + nginx (prod ready)
FROM webdevops/php-nginx:8.3

# Set working directory
WORKDIR /app

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R application:application /app/storage /app/bootstrap/cache

# Cache config/routes/views
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Link storage (si nécessaire)
RUN php artisan storage:link || true

# Expose port 80
EXPOSE 80

# Start supervisord (php-fpm + nginx)
CMD ["supervisord", "-n"]
