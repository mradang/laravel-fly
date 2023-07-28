#!/bin/bash

rotate=90

path=$(dirname $(readlink -f $0))

# 指定备份目录
backupDir=$path/storage/mysql
[ -d $backupDir ] || mkdir -p $backupDir

# 加载环境文件
source $path/docker/.env

# 备份
backupFile=$backupDir/$COMPOSE_PROJECT_NAME\_$(date +%Y%m%d%H%M%S).sql.gz
bash $path/fly exec mysql mysqldump app | gzip >$backupFile

# 清理旧的备份文件
rotate=$(($rotate + 1))
ls -t $backupDir/*.sql.gz | tail -n +$rotate | xargs -r rm -f
