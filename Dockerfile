# Base PHP + Apache image
FROM php:8.2-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install system dependencies including Oniguruma (for mbstring)
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    pkg-config \
    libonig-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev

# Install PHP extensions
RUN docker-php-ext-configure zip
RUN docker-php-ext-install pdo pdo_mysql mbstring zip

# Set working directory
WORKDIR /var/www/html

# Set Apache document root to public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf

# Copy application files
COPY . .

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ------------------------------------------------------------
# FIX: Force SQLite during build so Laravel does not try MySQL
# ------------------------------------------------------------
ENV DB_CONNECTION=sqlite
ENV DB_DATABASE=/tmp/database.sqlite

# Create fake sqlite file so Laravel does not crash
RUN touch /tmp/database.sqlite

# Now install dependencies (Laravel will NOT connect to MySQL)
RUN composer install --no-dev --optimize-autoloader

# Generate key (no fail if already exists)
RUN php artisan key:generate --force || true

# Fix permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# ------------------------------------------------------------
# RUNTIME CONFIG — Render environment variables take over
# These override the build-time SQLite settings
# ------------------------------------------------------------
ENV DB_CONNECTION=mysql

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
