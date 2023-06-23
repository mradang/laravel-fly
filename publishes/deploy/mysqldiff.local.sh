#!/bin/bash

# 当前路径
path=$(dirname $(readlink -f $0))
project=$(basename $(dirname $path))
cd $path/../

# 在新库中执行迁移
sed -i 's/^\(DB_DATABASE=.*\)/\1_new/' .env
php artisan migrate:fresh >>/dev/null 2>&1
php artisan fly:mysqlstruct >/tmp/$project.struct.json
sed -i 's/^\(DB_DATABASE=.*\)_new$/\1/' .env

# 对比库结构
php artisan fly:mysqldiff --baseStructFile=/tmp/$project.struct.json

# 清理
rm /tmp/$project.struct.json
