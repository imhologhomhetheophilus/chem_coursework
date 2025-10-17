# Use official PHP image with Apache
FROM php:8.2-apache

# Set working directory inside container
WORKDIR /var/www/html

# Enable Apache mod_rewrite (useful for clean URLs)
RUN a2enmod rewrite

# Copy your project into the container
# This will copy everything except items in .dockerignore
COPY . .

# Set the DocumentRoot to the public folder
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Give permissions to the uploads folder (if exists)
RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html/uploads && \
    chmod -R 755 /var/www/html/uploads

# Expose default Apache port
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
