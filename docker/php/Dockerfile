FROM php:8.4-fpm
# This is the correct base image - php:8.4-fpm runs PHP-FPM which works with Nginx
# Not using php:8.4-apache which would run Apache

RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libonig-dev \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

RUN pecl install -o -f redis \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis

# Install nvm and Node 23.11.0
ENV NVM_DIR="/root/.nvm"
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.3/install.sh | bash \
    && . "$NVM_DIR/nvm.sh" \
    && nvm install 23.11.0 \
    && nvm alias default 23.11.0
ENV PATH="$NVM_DIR/versions/node/v23.11.0/bin:$PATH"

WORKDIR /var/www/html

# Fix permissions for Laravel storage and bootstrap cache
RUN chown -R root:root /var/www/html
# Create the command to be run on container startup to set permissions
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose PHP-FPM port
EXPOSE 9000
# Expose Vite development server port
EXPOSE 5173

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]