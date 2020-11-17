#!/bin/bash

# 调用方法 .mysql.backup.download.sh configName

# 当前路径
path=$(dirname $(readlink -f $0))

# 配置文件
configFile=$path"/.publish.config."$1
envFile=$path"/../.env"

# 检查配置文件
[[ ! -f $configFile ]] && exit 0
source $configFile

# 备份
baseFileName=$1\_$DBNAME\_`date "+%Y%m%d%H%M%S"`
sqlFile=/tmp/$baseFileName.sql
ssh -p $PORT root@$HOST "mysqldump $DBNAME > $sqlFile"
ssh -p $PORT root@$HOST "zip -j -q $sqlFile.zip $sqlFile"

# 下载
scp -P $PORT root@$HOST:$sqlFile.zip /d/

# 删除备份
ssh -p $PORT root@$HOST "rm $sqlFile -f"
ssh -p $PORT root@$HOST "rm $sqlFile.zip -f"

# 解压
unzip -q /d/$baseFileName.sql.zip -d /d/
rm /d/$baseFileName.sql.zip -f
