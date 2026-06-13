#!/bin/sh
set -e

# Dynamically set Apache port for Render
if [ -n "$PORT" ]; then
  echo "Configuring Apache to listen on port $PORT..."
  sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
  sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/g" /etc/apache2/sites-available/000-default.conf
fi

# Ensure storage directories exist and have proper permissions
echo "Setting up storage directory structure..."
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs

# Handle SQLite database file creation if using SQLite
if [ "$DB_CONNECTION" = "sqlite" ]; then
  # Extract DB_DATABASE or default to database/database.sqlite
  DB_FILE=${DB_DATABASE:-/var/www/html/database/database.sqlite}
  if [ ! -f "$DB_FILE" ]; then
    echo "Creating SQLite database file at $DB_FILE..."
    mkdir -p "$(dirname "$DB_FILE")"
    touch "$DB_FILE"
  fi
  chown -R www-data:www-data "$(dirname "$DB_FILE")"
  chmod -R 775 "$(dirname "$DB_FILE")"
fi

# Set ownership and permissions for Laravel writable directories
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Clear any cached bootstrap configurations
echo "Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache configuration and routes for production runtime performance
echo "Caching configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "Running migrations..."
php artisan migrate --force

# Execute the main container command (Apache)
echo "Starting Apache..."
exec apache2-foreground "$@"
