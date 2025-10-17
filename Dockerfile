# Use official PHP 8.2 with Apache
FROM php:8.2-apache

# Enable PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy all project files
COPY . .

# Set Apache DocumentRoot to public folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Update Apache configuration to point to public/
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
