# 1. Use PHP with Apache
FROM php:8.2-apache

# 2. Install mysqli for database support
RUN docker-php-ext-install mysqli

# 3. Enable Apache rewrite (important for routing)
RUN a2enmod rewrite

# 4. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Copy app files
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# 6. Install PHP dependencies
RUN composer install --no-dev --prefer-dist --no-interaction

# 7. Expose Render's port
EXPOSE 10000

# 8. Start Apache
CMD ["apache2-foreground"]

