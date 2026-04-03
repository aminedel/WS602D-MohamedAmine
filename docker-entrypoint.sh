#!/bin/bash
set -e

export APP_ENV=${APP_ENV:-prod}
PORT=${PORT:-8080}

if [ -n "$DATABASE_URL" ]; then
    echo "Running database setup..."
    php bin/console doctrine:schema:update --force --no-interaction 2>/dev/null || true
    echo "Database ready."
fi

php bin/console cache:clear --env=$APP_ENV 2>/dev/null || true

echo "Starting PHP server on port $PORT..."
exec php -S 0.0.0.0:$PORT -t public public/router.php
