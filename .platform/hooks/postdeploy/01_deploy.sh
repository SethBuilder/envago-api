#!/bin/bash
set -e

# Load EB environment variables (not available in hooks by default)
source /opt/elasticbeanstalk/deployment/env

APP=/var/app/current

mkdir -p "$APP/storage/framework/sessions" \
         "$APP/storage/framework/views" \
         "$APP/storage/framework/cache/data" \
         "$APP/storage/app/public" \
         "$APP/storage/logs" \
         "$APP/bootstrap/cache"

cd "$APP"
if [ "${FRESH_MIGRATE}" = "true" ]; then
    php artisan migrate:fresh --force --seed
else
    php artisan migrate --force
fi
php artisan storage:link --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R webapp:webapp "$APP/storage" "$APP/bootstrap/cache"
chmod -R 775 "$APP/storage" "$APP/bootstrap/cache"
