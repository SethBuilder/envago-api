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
# php artisan migrate --force  # temporarily disabled — run manually after this deploy
php artisan storage:link --force
php artisan config:clear
php artisan route:cache
php artisan view:cache

# chown after artisan so cache files created by root are accessible to webapp
chown -R webapp:webapp "$APP/storage" "$APP/bootstrap/cache"
