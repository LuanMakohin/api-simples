#!/bin/bash

if [ ! -d "vendor" ]; then
    composer install
fi

chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

exec "$@"
