FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY . .

RUN composer install

RUN composer require pcntl

CMD ["php", "index.php"]
