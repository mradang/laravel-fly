#!/bin/bash

# 需在 mysql 中添加用户 abc，授予全局查询权限
# 调用方法 .mysqldiff.remote.sh configName

# 配置文件
configFile=".publish.config."$1

# 检查配置文件
[[ ! -f $configFile ]] && exit 0
source $configFile

# 本地数据库配置
DB_HOST=`cat .env | grep ^DB_HOST= | awk -F= '{print $2}'`
DB_PORT=`cat .env | grep ^DB_PORT= | awk -F= '{print $2}'`
DB_DATABASE=`cat .env | grep ^DB_DATABASE= | awk -F= '{print $2}'`
DB_USERNAME=`cat .env | grep ^DB_USERNAME= | awk -F= '{print $2}'`
DB_PASSWORD=`cat .env | grep ^DB_PASSWORD= | awk -F= '{print $2}'`

# 开启远程访问
if [ $MYSQL_FRIEWALL = true ] ; then
    ssh -p $PORT root@$HOST "firewall-cmd --add-service=mysql"
fi

# 在新库中执行迁移
sed -i 's/^\(DB_DATABASE=.*\)/\1_new/' .env
php artisan migrate:fresh >> /dev/null 2>&1
sed -i 's/^\(DB_DATABASE=.*\)_new$/\1/' .env

# 与远程库比较
php artisan fly:mysqldiff --host1=$DB_HOST:$DB_PORT --dbname1=${DB_DATABASE}_new --auth1=$DB_USERNAME:$DB_PASSWORD --host2=$HOST:3306 --dbname2=$DBNAME --auth2=abc:def

# 关闭远程访问
if [ $MYSQL_FRIEWALL = true ] ; then
    ssh -p $PORT root@$HOST "firewall-cmd --remove-service=mysql"
fi
