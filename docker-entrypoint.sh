#!/bin/bash
set -e

export APP_ENV=${APP_ENV:-prod}

# Run migrations if DATABASE_URL is set
if [ -n "$DATABASE_URL" ]; then
    echo "Running database setup..."
    php bin/console doctrine:database:create --if-not-exists --no-interaction 2>/dev/null || true
    php bin/console doctrine:schema:update --force --no-interaction 2>/dev/null || true

    # Load fixtures only if tables are empty
    COUNT=$(php bin/console doctrine:query:sql "SELECT COUNT(*) as c FROM plan" 2>/dev/null | grep -o '[0-9]*' | head -1 || echo "0")
    if [ "$COUNT" = "0" ] || [ -z "$COUNT" ]; then
        echo "Loading fixtures..."
        php bin/console doctrine:fixtures:load --no-interaction 2>/dev/null || true
    fi
    echo "Database ready."
fi

# Clear and warmup cache
php bin/console cache:clear 2>/dev/null || true

# Fix permissions
chown -R www-data:www-data var public/uploads 2>/dev/null || true

echo "Starting Apache on port ${PORT:-80}..."
exec apache2-foreground
