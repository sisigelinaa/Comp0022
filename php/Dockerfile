FROM php:7.4-apache
COPY . /var/www/html/
RUN docker-php-ext-install mysqli
EXPOSE 80

# # Use an official PHP image with Apache
# FROM php:8.1-apache

# # Enable MySQLi extension
# RUN docker-php-ext-install mysqli

# # Install Python and dependencies
# RUN apt-get update && apt-get install -y \
#     python3 \
#     python3-pip \
#     python3-venv \
#     && rm -rf /var/lib/apt/lists/*  # Clean up to reduce image size

# # Ensure python3 is the default Python
# RUN ln -s /usr/bin/python3 /usr/bin/python

# # Copy application files
# COPY . /var/www/html/

# # Set permissions
# RUN chown -R www-data:www-data /var/www/html
