FROM php:8.4-fpm-alpine3.21

RUN curl -s https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
RUN apk update && apk add --no-cache $PHPIZE_DEPS linux-headers
RUN pecl install xdebug
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-enable xdebug

COPY ./docker/php/php.ini /usr/local/etc/php/php.ini

WORKDIR /srv/app
