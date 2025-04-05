#!/bin/bash

if [ ! -d "vendor" ]; then
  echo "🔧 Instalando dependências do Laravel..."
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

exec apache2-foreground
