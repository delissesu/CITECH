FROM php:8.3-fpm-alpine AS builder

RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    bash \
    curl \
    git \
    libzip-dev \
    icu-dev \
    libxml2-dev \
    zlib-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    openssl-dev \
    oniguruma-dev \
    sqlite-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
  && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    pdo_sqlite \
    zip \
    gd \
    bcmath \
    intl \
    pcntl \
    xml \
    opcache \
  && pecl install redis \
  && docker-php-ext-enable redis \
  && apk add --no-cache --virtual .composer-deps curl \
  && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
  && apk del .composer-deps \
  && rm -rf /var/cache/apk/* /tmp/pear ~/.pearrc

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-dev --no-scripts --no-progress --no-interaction \
  && composer clear-cache

FROM builder AS node-build
RUN apk add --no-cache nodejs npm
WORKDIR /var/www/html
COPY package.json package-lock.json ./
COPY . .
RUN npm ci
RUN npm run build

FROM php:8.3-fpm-alpine AS production
RUN apk add --no-cache \
    bash \
    curl \
    libzip \
    icu-libs \
    libpng \
    libjpeg-turbo \
    libwebp \
    freetype \
    zlib \
    oniguruma \
    openssl \
    libxml2 \
  && apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    libzip-dev \
    icu-dev \
    libxml2-dev \
    zlib-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    oniguruma-dev \
    sqlite-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
  && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    pdo_sqlite \
    zip \
    gd \
    bcmath \
    intl \
    pcntl \
    xml \
    opcache \
  && pecl install redis \
  && docker-php-ext-enable redis \
  && apk del .build-deps \
  && rm -rf /var/cache/apk/* /tmp/pear ~/.pearrc

WORKDIR /var/www/html
COPY --from=builder /var/www/html/vendor ./vendor
COPY --from=builder /usr/local/bin/composer /usr/local/bin/composer
COPY --from=node-build /var/www/html/public/build ./public/build
COPY . .

RUN composer dump-autoload --optimize --classmap-authoritative --no-dev --no-interaction \
  && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
