#!/bin/bash

# 调用方法 mysqldiff.remote.sh configName

# 当前路径
path=$(dirname $(readlink -f $0))
configName=$1
project=$(basename $(dirname $path))
cd $path/../

# 检查发布配置文件
configFile=$path/.publish.config.$configName
if [ ! -e $configFile ]; then
    echo "未找到发布配置文件$configFile"
    exit
fi
source $configFile

# 在新库中执行迁移
sed -i 's/^\(DB_DATABASE=.*\)/\1_new/' .env
php artisan migrate:fresh >>/dev/null 2>&1
php artisan fly:mysqlstruct >/tmp/$project.struct.json
sed -i 's/^\(DB_DATABASE=.*\)_new$/\1/' .env

# 传送库结构到宿主端app目录
scp -P $PORT /tmp/$project.struct.json $USER@$HOST:/var/www/$KEY/serve/mysql.base_struct.json

# 远程库比较
docker_php="docker-compose exec php /usr/local/bin/php"
struct_file=/var/www/html/mysql.base_struct.json
ssh -p $PORT $USER@$HOST "cd /docker/$KEY; $docker_php artisan fly:mysqldiff --baseStructFile=$struct_file"

# 清理
rm /tmp/$project.struct.json -f
