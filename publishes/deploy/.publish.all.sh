#!/bin/bash

# 当前路径
path=$(dirname $(readlink -f $0))

# 配置文件
files=($(ls -la $path/.publish.config.* | grep -v 'example' | awk {'print $9'}))

# 倒计时
for i in $(seq 5|tac);do
    echo -en "$i秒后开始更新「全部」...\r"
    sleep 1
done
start=$(date "+%s")

# 循环处理配置文件
for configFile in "${files[@]}" ; do
    source $configFile

    # 调用发布脚本
    echo -e "\033[K开始更新「$NAME」..."
    $path/.publish.sh $configFile
done

# 计时
now=$(date "+%s")
time=$((now-start))

# 完成
dateTime=`date "+%Y-%m-%d %H:%M:%S"`
echo "$dateTime 「全部」更新完成，耗时 $time 秒."
