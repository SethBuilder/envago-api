#!/bin/bash
set -e

APP=/var/app/current

# Always runs first — permissions must be set before anything else
mkdir -p "$APP/storage/framework/sessions" \
         "$APP/storage/framework/views" \
         "$APP/storage/framework/cache/data" \
         "$APP/storage/app/public" \
         "$APP/storage/logs" \
         "$APP/bootstrap/cache"
chown -R webapp:webapp "$APP/storage" "$APP/bootstrap/cache"
chmod -R 775 "$APP/storage" "$APP/bootstrap/cache"

cd "$APP"
su webapp -s /bin/bash -c "php artisan migrate --force"
su webapp -s /bin/bash -c "php artisan storage:link --force"
su webapp -s /bin/bash -c "php artisan config:cache"
su webapp -s /bin/bash -c "php artisan route:cache"
su webapp -s /bin/bash -c "php artisan view:cache"
