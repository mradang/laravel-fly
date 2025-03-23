FROM php:8.2-fpm-alpine

ARG ENV
ARG WWWUSER
ARG WWWGROUP

COPY after_publish.sh /usr/local/bin/
COPY supervisor.$ENV.ini /etc/supervisor.d/supervisor.ini

RUN echo "http://mirrors.aliyun.com/alpine/latest-stable/main/" >/etc/apk/repositories && \
    echo "http://mirrors.aliyun.com/alpine/latest-stable/community/" >>/etc/apk/repositories && \
    apk update && \
    apk upgrade && \
    apk add gcc g++ make autoconf libzip-dev supervisor shadow && \
    docker-php-ext-install zip bcmath pdo_mysql && \
    curl https://pecl.php.net/get/redis-5.3.7.tgz -o /tmp/redis-5.3.7.tgz && \
    printf 'no\nno\n' | pecl install -o /tmp/redis-5.3.7.tgz && \
    docker-php-ext-enable redis && \
    apk del gcc g++ make autoconf && \
    rm -rf /tmp/*.tgz /var/cache/apk/* /tmp/pear/ && \
    mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    curl https://getcomposer.org/download/2.5.8/composer.phar -o /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer && \
    chmod +x /usr/local/bin/after_publish.sh && \
    echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" >>/var/spool/cron/crontabs/www-data && \
    usermod -u $WWWUSER www-data && groupmod -g $WWWGROUP www-data

WORKDIR /var/www/html

CMD ["supervisord"]
