#!/bin/sh

if [ ! -f /var/www/backend/vendor/autoload.php ]; then
    composer install --no-interaction --optimize-autoloader
fi

if [ ! -f /var/www/backend/.env ]; then
    cp /var/www/backend/.env.example /var/www/backend/.env
    php /var/www/backend/artisan key:generate
fi

php /var/www/backend/artisan storage:link --force 2>/dev/null || true

php-fpm -D

nginx -g "daemon off;"
