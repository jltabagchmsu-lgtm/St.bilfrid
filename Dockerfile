# ==============================================================================
#  Dockerfile with FrankenPHP (Caddy + PHP Runtime in a single container)
# ==============================================================================
FROM dunglas/frankenphp:1-php8.1

# Install required PHP extensions
RUN install-php-extensions \
    pdo_sqlite \
    sqlite3 \
    bcmath \
    gd \
    intl \
    zip \
    opcache

# Copy application files
WORKDIR /app
COPY . /app

# Configure permissions
RUN mkdir -p /app/storage /app/bootstrap/cache /app/database \
    && chmod -R 775 /app/storage /app/bootstrap/cache /app/database

# Set environment
ENV SERVER_NAME=":8000"
ENV FRANKENPHP_CONFIG="worker ./public/index.php"
ENV TRUSTED_PROXIES="*"

# Expose port
EXPOSE 8000

# Start FrankenPHP with Caddy (FrankenPHP-native Caddyfile with php_server).
# NOTE: ./Caddyfile is for local stock Caddy + php-cgi/php-fpm dev only.
CMD ["frankenphp", "run", "--config", "/app/Caddyfile.frankenphp"]
