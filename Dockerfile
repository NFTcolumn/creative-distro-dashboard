FROM php:8.2-apache

# Install required PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy application files
COPY backend/ /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
