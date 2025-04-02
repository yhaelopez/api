#!/bin/bash
set -e

# Set permissions for Laravel storage and bootstrap cache directories
if [ -d /var/www/html/storage ]; then
    chmod -R 775 /var/www/html/storage
    chown -R www-data:www-data /var/www/html/storage
fi

if [ -d /var/www/html/bootstrap/cache ]; then
    chmod -R 775 /var/www/html/bootstrap/cache
    chown -R www-data:www-data /var/www/html/bootstrap/cache
fi

# Execute the passed command
exec "$@"
