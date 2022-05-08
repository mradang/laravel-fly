#!/bin/bash

# 基础环境
# 1. 在服务端 /home 目录中已 clone 本项目
# 2. 已部署 php 和 /usr/local/bin/composer.phar

# 调用方法
# .publish.sh host port dir

# 路径
path=$(dirname $(readlink -f $0))
basepath=$(dirname $path)

# 配置参数
project=$(basename $basepath)
host=$1
port=$2
publish=$3

# 执行发布
ssh -p $port root@$host "cd /home/$project/; git pull"

ssh -p $port root@$host "rm /home/$project.temp/ -rf"
ssh -p $port root@$host "mkdir /home/$project.temp"
ssh -p $port root@$host "\cp /home/$project/* /home/$project.temp/ -a"
ssh -p $port root@$host "cd /home/$project.temp/; php /usr/local/bin/composer.phar --no-dev install"

ssh -p $port root@$host "cd /var/www/$publish/; rm config/ -rf; rm app/ -rf; rm vendor/ -rf;"
ssh -p $port root@$host "\cp /home/$project.temp/* /var/www/$publish/ -a"

ssh -p $port root@$host "restorecon -RF /var/www/$publish/"
ssh -p $port root@$host "chmod a+rw /var/www/$publish/storage -R"
ssh -p $port root@$host "chcon -t httpd_sys_rw_content_t /var/www/$publish/storage -R >> /dev/null 2>&1"
ssh -p $port root@$host "chown apache:apache /var/www/$publish/storage/logs/*"

ssh -p $port root@$host "cd /var/www/$publish/; php artisan route:cache"

ssh -p $port root@$host "cd /var/www/$publish/; php artisan rbac:RefreshRbacNode"

ssh -p $port root@$host "systemctl restart supervisord"
