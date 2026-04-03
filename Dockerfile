FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libcurl4-openssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql intl zip gd mbstring xml curl opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Fix Apache MPM conflict and enable mod_rewrite
RUN a2dismod mpm_event 2>/dev/null || true \
    && a2enmod mpm_prefork rewrite

# Set Apache DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Allow .htaccess
RUN echo '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>' > /etc/apache2/conf-available/symfony.conf \
    && a2enconf symfony

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy project files
COPY . .

# Force prod environment during build
ENV APP_ENV=prod

# Install dependencies (no-dev) without running scripts
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs --no-scripts

# Create required directories
RUN mkdir -p var/cache var/log public/uploads/pdfs \
    && chown -R www-data:www-data var public/uploads

# Warmup cache in prod mode
RUN php bin/console cache:clear --env=prod --no-warmup 2>/dev/null || true
RUN php bin/console cache:warmup --env=prod 2>/dev/null || true

# Use PORT env variable from Railway
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE ${PORT}

CMD ["docker-entrypoint.sh"]
