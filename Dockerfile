# Dockerfile
FROM php:8.3-fpm-alpine

# Installer les dépendances pour PDO MySQL et utilitaires
RUN apk add --no-cache \
        bash \
        curl \
        mariadb-connector-c-dev \
        libzip-dev \
        zip \
        unzip \
        git \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql \
    && rm -rf /var/cache/apk/*

# Créer un utilisateur non-root
RUN addgroup -g 1000 -S www && adduser -u 1000 -S www -G www

# Définir le dossier de travail
WORKDIR /var/www/html

# Copier le projet et donner les droits à l'utilisateur www
COPY --chown=www:www . .

# Config PHP-FPM pour l'utilisateur www
RUN echo '[www]' > /usr/local/etc/php-fpm.d/www.conf && \
    echo 'user = www' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'group = www' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'listen = 9000' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'pm = ondemand' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'pm.max_children = 10' >> /usr/local/etc/php-fpm.d/www.conf

USER www

EXPOSE 9000

CMD ["php-fpm"]
