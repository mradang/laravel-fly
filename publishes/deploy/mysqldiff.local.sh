#!/bin/bash

# 当前路径
path=$(dirname $(readlink -f $0))
project=$(basename $(dirname $path))
cd $path/../

# 检查容器是否运行
DOCKER_COMPOSE=(docker-compose)
DOCKER_COMPOSE+=(-f "$path/../docker/docker-compose.yml")
if [ -z "$("${DOCKER_COMPOSE[@]}" ps -q)" ]; then
    echo "容器未运行，请使用以下命令运行容器：'fly up' or 'fly up -d'" >&2
    exit 1
fi

# 在新库中执行迁移
sed -i 's/^\(DB_DATABASE=.*\)/\1_new/' .env
bash fly artisan migrate:fresh >>/dev/null 2>&1
bash fly artisan fly:mysqlstruct >$(pwd)/$project.struct.json
sed -i 's/^\(DB_DATABASE=.*\)_new$/\1/' .env

# 对比库结构
bash fly artisan fly:mysqldiff --baseStructFile=/var/www/html/$project.struct.json

# 清理
rm $(pwd)/$project.struct.json
