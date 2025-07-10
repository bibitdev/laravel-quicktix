FROM serversideup/php:8.3-fpm-nginx AS base

# Switch to root so we can do root things
USER root

# Install required PHP extensions
RUN install-php-extensions exif gd intl

# Install Node.js and enable corepack
ARG NODE_VERSION=20.18.0
ENV PATH=/usr/local/node/bin:$PATH
RUN curl -sL https://github.com/nodenv/node-build/archive/master.tar.gz | tar xz -C /tmp/ && \
    /tmp/node-build-master/bin/node-build "${NODE_VERSION}" /usr/local/node && \
    corepack enable && \
    rm -rf /tmp/node-build-master

# Drop back to unprivileged user
USER www-data

FROM base

ENV SSL_MODE="off"
ENV AUTORUN_ENABLED="true"
ENV PHP_OPCACHE_ENABLE="1"
ENV HEALTHCHECK_PATH="/up"

# Copy the app files
COPY --chown=www-data:www-data . /var/www/html
RUN mkdir -p /var/www/html/storage/app/public && \
    chown -R www-data:www-data /var/www/html/storage

# Run Composer install
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Precompile assets
RUN npm install && \
    npm run build && \
    rm -rf node_modules
