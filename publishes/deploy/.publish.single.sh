#!/bin/bash

# 调用方法 .publish.sh configName

# 当前路径
path=$(dirname $(readlink -f $0))

# 配置文件
configFile=$path"/.publish.config."$1

# 检查配置文件
[[ ! -f $configFile ]] && exit 0
source $configFile

# 倒计时
for i in $(seq 5|tac);do
    echo -en "$i秒后开始更新「$NAME」...\r"
    sleep 1
done
echo -e "\033[K开始更新「$NAME」..."
start=$(date "+%s")

# 调用发布脚本
$path/.publish.sh $HOST $PORT $DIR

# 计时
now=$(date "+%s")
time=$((now-start))

# 完成
dateTime=`date "+%Y-%m-%d %H:%M:%S"`
echo "$dateTime 「$NAME」更新完成，耗时 $time 秒."
