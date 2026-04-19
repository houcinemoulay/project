#!/usr/bin/env bash
# Exit on error
set -o errexit

echo "Building Laravel app..."

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies and build assets
npm install
npm run build

# Run migrations (ensure DB is ready)
# Note: Migrations are better run in the start command or as a separate step,
# but we'll include them here or in the render.yaml for clarity.
# php artisan migrate --force

echo "Build finished!"
