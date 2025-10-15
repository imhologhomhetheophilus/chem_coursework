RUN apt-get update && apt-get install -y php8.2-mysql

# Use the official PHP image
FROM php:8.2-apache

# Copy your project files into the Apache web root
COPY . /var/www/html/

# Expose port 80 (default web port)
EXPOSE 80

# Start Apache when the container starts
CMD ["apache2-foreground"]
# RUN apt-get update && apt-get install -y php-mysql
