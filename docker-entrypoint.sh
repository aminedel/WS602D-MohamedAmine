#!/bin/bash
set -e

export APP_ENV=${APP_ENV:-prod}

if [ -n "$DATABASE_URL" ]; then
    echo "Running database setup..."
    php bin/console doctrine:schema:update --force --no-interaction 2>/dev/null || true

    # Load initial data if plans table is empty
    COUNT=$(php bin/console doctrine:query:sql "SELECT COUNT(*) as c FROM plan" --no-interaction 2>/dev/null | grep -o '[0-9]*' | head -1 || echo "0")
    if [ "$COUNT" = "0" ] || [ -z "$COUNT" ]; then
        echo "Loading initial data..."
        php bin/console doctrine:query:sql "$(cat /var/www/html/init-data.sql)" --no-interaction 2>/dev/null || true
    fi
    echo "Database ready."
fi

php bin/console cache:clear --env=$APP_ENV 2>/dev/null || true
chown -R www-data:www-data var public/uploads 2>/dev/null || true

echo "Starting Apache on port ${PORT:-80}..."
exec apache2-foreground
