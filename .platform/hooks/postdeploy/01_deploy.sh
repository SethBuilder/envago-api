#!/bin/bash
set -e

APP=/var/app/current

mkdir -p "$APP/storage/framework/sessions" \
         "$APP/storage/framework/views" \
         "$APP/storage/framework/cache/data" \
         "$APP/storage/app/public" \
         "$APP/storage/logs" \
         "$APP/bootstrap/cache"

chown -R webapp:webapp "$APP/storage" "$APP/bootstrap/cache"
chmod -R 775 "$APP/storage" "$APP/bootstrap/cache"
touch "$APP/storage/logs/laravel.log"
chown webapp:webapp "$APP/storage/logs/laravel.log"
chmod 664 "$APP/storage/logs/laravel.log"

cd "$APP"
php artisan migrate --force
php artisan storage:link --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
