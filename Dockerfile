FROM php:8.2-apache

# Install mysqli extension for MySQL database connectivity
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy application files to Apache public directory
COPY . /var/www/html/

# Expose port 80
EXPOSE 80
