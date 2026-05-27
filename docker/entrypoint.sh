#!/bin/sh
set -e

# Cache configuration, routes, and views for production performance
echo "Caching Laravel assets..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Publish Livewire assets for high-performance direct Nginx static serving
echo "Publishing Livewire assets..."
php artisan livewire:publish --assets --ansi --no-interaction

# Run database migrations (force in production)
if [ "$RUN_MIGRATIONS" = "true" ] || [ "$NODE_ENV" = "production" ] || [ "$APP_ENV" = "production" ]; then
    echo "Running database migrations..."
    php artisan migrate --force --no-interaction
    
    echo "Cleaning up duplicate job listings..."
    php artisan jobs:remove-duplicates
fi

# Execute CMD passed to docker container
echo "Starting application process..."
exec "$@"
