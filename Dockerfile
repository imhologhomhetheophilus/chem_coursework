# Use the official PHP image with Apache pre-installed
FROM php:8.2-apache

# Install MySQL extension (mysqli + pdo_mysql)
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev zip git unzip && \
    docker-php-ext-install mysqli pdo pdo_mysql && \
    docker-php-ext-enable mysqli

# Copy project files into the web root
COPY . /var/www/html/

# Set correct working directory
WORKDIR /var/www/html/

# Expose port 80 for web traffic
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
