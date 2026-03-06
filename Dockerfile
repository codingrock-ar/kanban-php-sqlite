FROM php:8.2-apache

# Install SQLite PDO extension
RUN apt-get update && apt-get install -y libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Update Apache configuration to allow .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Ensure the database file is writable
RUN touch database.sqlite && chmod 666 database.sqlite && chown www-data:www-data database.sqlite

EXPOSE 80
