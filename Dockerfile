FROM php:8.2-apache

# Enable Apache mod_rewrite (optional)
RUN a2enmod rewrite

# Install curl extension
RUN docker-php-ext-install curl

# Copy project files to Apache root
COPY index.php /var/www/html/index.php

EXPOSE 80

CMD ["apache2-foreground"]
