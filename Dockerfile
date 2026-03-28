FROM php:8.1-apache

# Set working directory
WORKDIR /var/www/html

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    curl \
    libcurl4-openssl-dev \
    php-curl \
    php-mysqli \
    php-xml \
    php-json \
    php-mbstring \
    git \
    unzip \
    && docker-php-ext-install -j$(nproc) \
    curl \
    mysqli \
    pdo \
    pdo_mysql \
    && a2enmod rewrite

# Enable Apache modules
RUN a2enmod rewrite && \
    a2enmod headers && \
    a2enmod php8.1

# Copy Apache configuration
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 777 /var/www/html/uploads && \
    chmod -R 777 /var/www/html/documents

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
