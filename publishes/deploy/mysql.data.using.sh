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

# 检查容器是否运行
DOCKER_COMPOSE=(docker compose)
DOCKER_COMPOSE+=(-f "$path/../docker/docker-compose.yml")
if [ -z "$("${DOCKER_COMPOSE[@]}" ps -q)" ]; then
    echo "容器未运行，请使用以下命令运行容器：'fly up' or 'fly up -d'" >&2
    exit 1
fi

# 备份
baseFileName=$KEY\_$configName\_$(date "+%Y%m%d%H%M%S")
sqlFile=/tmp/$baseFileName.sql
docker_exec="cd /home/$USER/$KEY/www/serve/docker; docker compose exec"
ssh -p $PORT $USER@$HOST "$docker_exec mysql mysqldump app > $sqlFile"

# 打包下载
ssh -p $PORT $USER@$HOST "gzip $sqlFile"
scp -P $PORT $USER@$HOST:/tmp/$baseFileName.sql.gz /tmp/
ssh -p $PORT $USER@$HOST "rm /tmp/$baseFileName.sql.gz -f"

# 解压
gunzip /tmp/$baseFileName.sql.gz

# 导入
bash fly cp /tmp/$baseFileName.sql mysql:/tmp/
bash fly exec mysql mysql -e "use app; source /tmp/$baseFileName.sql;"
bash fly exec mysql rm /tmp/$baseFileName.sql

# 清理
rm /tmp/$baseFileName.sql
