#!/bin/bash

# 需在 mysql 中添加用户 abc，授予全局查询权限

DB_HOST=`cat .env | grep ^DB_HOST= | awk -F= '{print $2}'`
DB_PORT=`cat .env | grep ^DB_PORT= | awk -F= '{print $2}'`
DB_DATABASE=`cat .env | grep ^DB_DATABASE= | awk -F= '{print $2}'`
DB_USERNAME=`cat .env | grep ^DB_USERNAME= | awk -F= '{print $2}'`
DB_PASSWORD=`cat .env | grep ^DB_PASSWORD= | awk -F= '{print $2}'`

sed -i 's/^\(DB_DATABASE=.*\)/\1_new/' .env
php artisan migrate:fresh >> /dev/null 2>&1
sed -i 's/^\(DB_DATABASE=.*\)_new$/\1/' .env

php artisan fly:mysqldiff --host1=$DB_HOST:$DB_PORT --dbname1=${DB_DATABASE}_new --auth1=$DB_USERNAME:$DB_PASSWORD --host2=$DB_HOST:$DB_PORT --dbname2=$DB_DATABASE --auth2=$DB_USERNAME:$DB_PASSWORD
