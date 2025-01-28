# Use an official PHP image with Apache
FROM php:8.1-apache

# Enable MySQLi extension
RUN docker-php-ext-install mysqli

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html
