FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    && docker-php-ext-install \
    zip \
    intl \
    mbstring \
    bcmath \
    opcache \
    pcntl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN addgroup -g 1000 appuser && adduser -u 1000 -G appuser -s /bin/sh -D appuser

WORKDIR /var/www/html

RUN mkdir -p /var/www/html/storage/logs \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache \
    /var/www/html/bootstrap/cache

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
