#!/bin/bash

# 调用方法 mysql.backup.download.sh configName

# 当前路径
path=$(dirname $(readlink -f $0))
configName=$1

# 检查发布配置文件
configFile=$path/.publish.config.$configName
if [ ! -e $configFile ]; then
    echo "未找到发布配置文件$configFile"
    exit
fi
source $configFile

# 备份
baseFileName=$KEY\_$configName\_$(date "+%Y%m%d%H%M%S")
sqlFile=/tmp/$baseFileName.sql
docker_exec="cd /docker/$KEY; docker-compose exec"
ssh -p $PORT root@$HOST "$docker_exec mysql mysqldump app > $sqlFile"

# 打包下载
ssh -p $PORT root@$HOST "gzip $sqlFile"
scp -P $PORT root@$HOST:/tmp/$baseFileName.sql.gz /d/
ssh -p $PORT root@$HOST "rm /tmp/$baseFileName.sql.gz -f"

# 解压
gunzip /d/$baseFileName.sql.gz
