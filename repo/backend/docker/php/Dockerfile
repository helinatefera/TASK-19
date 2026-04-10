FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        zip \
        intl \
        mbstring \
        bcmath \
        opcache \
        pcntl \
    && apk del $PHPIZE_DEPS linux-headers

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN addgroup -g 1000 appgroup && adduser -u 1000 -G appgroup -D appuser

WORKDIR /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

RUN mkdir -p /var/www/html/storage/logs \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache \
    /var/www/html/bootstrap/cache

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
