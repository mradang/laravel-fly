#!/bin/bash

# 当前路径
path=$(dirname $(readlink -f $0))

# 配置文件
envFile=$path"/../.env"

DB_HOST=`cat $envFile | grep ^DB_HOST= | awk -F= '{print $2}'`
DB_PORT=`cat $envFile | grep ^DB_PORT= | awk -F= '{print $2}'`
DB_DATABASE=`cat $envFile | grep ^DB_DATABASE= | awk -F= '{print $2}'`
DB_USERNAME=`cat $envFile | grep ^DB_USERNAME= | awk -F= '{print $2}'`
DB_PASSWORD=`cat $envFile | grep ^DB_PASSWORD= | awk -F= '{print $2}'`

sed -i 's/^\(DB_DATABASE=.*\)/\1_new/' $envFile
php artisan migrate:fresh >> /dev/null 2>&1
sed -i 's/^\(DB_DATABASE=.*\)_new$/\1/' $envFile

php artisan fly:mysqldiff --host1=$DB_HOST:$DB_PORT --dbname1=${DB_DATABASE}_new --auth1=$DB_USERNAME:$DB_PASSWORD --host2=$DB_HOST:$DB_PORT --dbname2=$DB_DATABASE --auth2=$DB_USERNAME:$DB_PASSWORD
