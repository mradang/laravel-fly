#!/bin/sh

cd /var/www/html

php artisan config:cache
php artisan key:generate --force

php artisan config:cache
php artisan route:cache
php artisan event:cache

php artisan migrate:status | grep "Migration table not found" >/dev/null
if [ $? -eq 0 ]; then
    php artisan migrate
fi

php artisan rbac:RefreshRbacNode
