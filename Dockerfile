# Use official PHP with Apache image
FROM php:8.2-apache

# Install required PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory inside container
WORKDIR /var/www/html

# Copy project files to container
COPY . .

# Set Apache DocumentRoot to public folder
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Set proper permissions for uploads folder
RUN mkdir -p public/uploads && chown -R www-data:www-data public/uploads

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
