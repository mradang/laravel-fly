FROM php:8.1-fpm-alpine

RUN echo "http://mirrors.aliyun.com/alpine/latest-stable/main/" > /etc/apk/repositories && \
    echo "http://mirrors.aliyun.com/alpine/latest-stable/community/" >> /etc/apk/repositories

RUN apk update && \
    apk upgrade && \
    apk add gcc g++ make autoconf libzip-dev supervisor && \
    docker-php-ext-install zip bcmath pdo_mysql && \
    printf 'no\nno\n' | pecl install redis && \
    docker-php-ext-enable redis

WORKDIR /var/www/html

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY supervisor.ini /etc/supervisor.d/
RUN echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" >> /var/spool/cron/crontabs/root

CMD ["supervisord"]
