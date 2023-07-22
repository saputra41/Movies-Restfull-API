FROM php:8.1-fpm

RUN apt-get update && apt-get install -y default-mysql-client \
    && docker-php-ext-install pdo_mysql

WORKDIR /var/www
