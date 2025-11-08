FROM php:8.3-fpm-alpine

RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql \
    && rm -rf /var/cache/apk/*

# User non-root
RUN addgroup -g 1000 -S www && adduser -u 1000 -S www -G www

WORKDIR /var/www/html

COPY --chown=www:www . .

RUN mkdir -p /usr/local/etc/php-fpm.d && \
    echo '[www]' > /usr/local/etc/php-fpm.d/www.conf && \
    echo 'user = www' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'group = www' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'listen = 9000' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'pm = ondemand' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'pm.max_children = 10' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'php_admin_value[error_log] = /var/log/php_errors.log' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'php_admin_flag[log_errors] = on' >> /usr/local/etc/php-fpm.d/www.conf

USER www

EXPOSE 9000

CMD ["php-fpm"]