# ベースとなるイメージはphp:7.2-fpm(7.4.1-fpm)を指定します。
FROM php:8.0-fpm

# COPY php.init  （PHPの設定ファイル）をDockerコンテナ内にコピーするコマンド
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini

# Composer install  今回はマルチステージビルド方法
COPY --from=composer:2.0 /usr/bin/composer /usr/bin/composer

# install Node.js  今回はマルチステージビルド方法
COPY --from=node:16.3.0 /usr/local/bin /usr/local/bin
COPY --from=node:16.3.0 /usr/local/lib /usr/local/lib

RUN apt-get update && \
    apt-get -y install \
    git \
    zip \
    unzip \
    vim \
    && docker-php-ext-install pdo_mysql bcmath

# docker-compose exec app bashというコマンドでappコンテナの中に入った場合、/var/www/htmlがカレントディレクトリになります。
WORKDIR /var/www/html