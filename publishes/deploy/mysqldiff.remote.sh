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

# 检查容器是否运行
DOCKER_COMPOSE=(docker compose)
DOCKER_COMPOSE+=(-f "$path/../docker/docker-compose.yml")
if [ -z "$("${DOCKER_COMPOSE[@]}" ps -q)" ]; then
    echo "容器未运行，请使用以下命令运行容器：'fly up' or 'fly up -d'" >&2
    exit 1
fi

# 在新库中执行迁移
sed -i 's/^\(DB_DATABASE=.*\)/\1_new/' .env
bash fly artisan migrate:fresh >>/dev/null 2>&1
bash fly artisan fly:mysqlstruct >/tmp/$project.struct.json
sed -i 's/^\(DB_DATABASE=.*\)_new$/\1/' .env

# 传送库结构到宿主端app目录
scp -P $PORT /tmp/$project.struct.json $USER@$HOST:/home/$USER/$KEY/www/serve/mysql.base_struct.json

# 远程库比较
docker_php="docker compose exec php /usr/local/bin/php"
struct_file=/var/www/html/mysql.base_struct.json
ssh -p $PORT $USER@$HOST "cd /home/$USER/$KEY/www/serve/docker; $docker_php artisan fly:mysqldiff --baseStructFile=$struct_file"

# 清理
rm /tmp/$project.struct.json -f
