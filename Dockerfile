# ==========================================================
# Stage 1: Build Frontend Assets (Vite / Tailwind)
# ==========================================================
FROM node:20-alpine AS frontend-builder
WORKDIR /app
COPY package*.json tailwind.config.js postcss.config.js vite.config.js ./
COPY resources/ ./resources/
COPY public/ ./public/
RUN npm ci && npm run build

# ==========================================================
# Stage 2: Install Composer Dependencies
# ==========================================================
FROM composer:2.7 AS php-builder
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# ==========================================================
# Stage 3: Production Runtime
# ==========================================================
FROM php:8.3-fpm-alpine

# Set system environment variables
ENV PORT=80
ENV WORKDIR=/var/www/html
WORKDIR $WORKDIR

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    libzip-dev

# Install high-quality PHP Extensions via trusted helper
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions gd bcmath zip pdo_mysql pdo_pgsql opcache redis

# Configure PHP-FPM to run as the nginx user
RUN sed -i 's/user = www-data/user = nginx/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/group = www-data/group = nginx/g' /usr/local/etc/php-fpm.d/www.conf

# Copy configurations
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copy application files
COPY --chown=nginx:nginx . .

# Copy vendor folders from builder stage
COPY --from=php-builder --chown=nginx:nginx /app/vendor ./vendor
# Dump autoloader with optimal performance
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
RUN composer dump-autoload --no-dev --classmap-authoritative

# Copy compiled frontend assets from frontend-builder stage
COPY --from=frontend-builder --chown=nginx:nginx /app/public/build ./public/build

# Setup storage and bootstrap permissions
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache && \
    chown -R nginx:nginx storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Log directories setup
RUN mkdir -p /var/log/supervisor /var/log/nginx && \
    chown -R nginx:nginx /var/log/supervisor /var/log/nginx /var/lib/nginx

# Expose port
EXPOSE 80

# Configure entrypoint and main run command
ENTRYPOINT ["entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
