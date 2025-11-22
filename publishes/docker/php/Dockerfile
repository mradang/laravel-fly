FROM php:8.3-fpm-alpine

ARG ENV
ARG WWWUSER
ARG WWWGROUP

COPY after_publish.sh /usr/local/bin/
COPY supervisor.$ENV.ini /etc/supervisord.conf

RUN echo "http://mirrors.aliyun.com/alpine/latest-stable/main/" >/etc/apk/repositories && \
    echo "http://mirrors.aliyun.com/alpine/latest-stable/community/" >>/etc/apk/repositories && \
    apk update && \
    apk upgrade && \
    # 安装 Linux 编译工具
    apk add gcc g++ make autoconf libzip-dev shadow && \
    # （2025-11-06）因 apk add supervisor 只能安装到4.2.5版，所以使用 pip 安装最新版4.3.0
    apk add --no-cache py3-pip && \
    python -m venv /opt/venv && \
    . /opt/venv/bin/activate && \
    pip install --no-cache-dir supervisor && \
    ln -s /opt/venv/bin/supervisorctl /usr/bin/supervisorctl && \
    # 安装 PHP 常用扩展
    docker-php-ext-install zip bcmath pdo_mysql && \
    # 清理 Linux 编译工具
    apk del gcc g++ make autoconf && \
    rm -rf /tmp/*.tgz /var/cache/apk/* /tmp/pear/ && \
    # 配置 PHP 环境
    mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    curl https://getcomposer.org/download/2.9.2/composer.phar -o /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer && \
    chmod +x /usr/local/bin/after_publish.sh && \
    echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" >>/var/spool/cron/crontabs/www-data && \
    usermod -u $WWWUSER www-data && groupmod -g $WWWGROUP www-data

WORKDIR /var/www/html

CMD ["/opt/venv/bin/supervisord", "-c", "/etc/supervisord.conf"]
