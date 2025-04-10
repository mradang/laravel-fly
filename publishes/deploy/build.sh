#!/bin/bash

# 调用方法
# build.sh [config_name config_name ...]

_build() {
    # 常量参数
    path=$1
    configName=$2

    # 读取发布配置
    configFile=$path/.publish.config.$configName
    if [ ! -e $configFile ]; then
        echo "未找到发布配置文件$configFile"
        return 1
    fi
    source $configFile

    # 复制 docker 文件到临时目录
    mkdir -p /tmp/$KEY
    \cp $path/../docker /tmp/$KEY -a
    \cp $path/../docker/.env.example /tmp/$KEY/docker/.env
    cd /tmp/$KEY/docker

    # 生成配置文件
    server_dir=/home/$USER/$KEY

    WWWUSER=$(ssh -p $PORT $USER@$HOST 'echo $(id -u)')
    WWWGROUP=$(ssh -p $PORT $USER@$HOST 'echo $(id -g)')

    sed -i "s|COMPOSE_PROJECT_NAME=.*|COMPOSE_PROJECT_NAME=${KEY}|" .env
    sed -i "s|WWWUSER=.*|WWWUSER=${WWWUSER}|" .env
    sed -i "s|WWWGROUP=.*|WWWGROUP=${WWWGROUP}|" .env
    sed -i "s|NGINX_PORT=.*|NGINX_PORT=${NGINX_PORT}|" .env
    sed -i "s|MYSQL_PORT=.*|MYSQL_PORT=${MYSQL_PORT}|" .env
    PASSWORD=$(printf '%s\n' "$MYSQL_ROOT_PASSWORD" | sed -e 's/[\/&]/\\&/g')
    sed -i "s|MYSQL_ROOT_PASSWORD=.*|MYSQL_ROOT_PASSWORD=${PASSWORD}|" .env
    sed -i "s|MYSQL_DATA_VOLUME=.*|MYSQL_DATA_VOLUME=${server_dir}/mysql_data|" .env
    sed -i "s|CODE_VOLUME=.*|CODE_VOLUME=${server_dir}/www/serve|" .env

    # 构建镜像
    docker compose build
    docker image prune -f

    # 导出镜像
    for dockerfile in $(find . -name "Dockerfile"); do
        image_name=$(basename $(dirname $dockerfile))
        docker save $KEY-$image_name | gzip >/tmp/$KEY-$image_name.tar.gz
        scp -P $PORT /tmp/$KEY-$image_name.tar.gz $USER@$HOST:/tmp
        ssh -p $PORT $USER@$HOST "docker load -i /tmp/$KEY-$image_name.tar.gz"
        ssh -p $PORT $USER@$HOST "rm /tmp/$KEY-$image_name.tar.gz -f"
        rm /tmp/$KEY-$image_name.tar.gz -f
    done

    # 创建程序代码目录
    ssh -p $PORT $USER@$HOST "mkdir -p $server_dir/www/serve"

    # 复制 docker 编排文件目录到宿主机
    scp -P $PORT -r /tmp/$KEY/docker/ $USER@$HOST:$server_dir/www/serve

    # 清理本地 docker 临时目录
    cd /tmp
    rm /tmp/$KEY -rf

    # 启动
    ssh -p $PORT $USER@$HOST "cd $server_dir/www/serve/docker; docker compose stop; docker compose up -d"
    ssh -p $PORT $USER@$HOST "docker image prune -f"

    echo "$configName 已构建"
}

# 倒计时
for i in $(seq 5 | tac); do
    echo -en $i"秒后开始构建...\r"
    sleep 1
done
start=$(date "+%s")

# 主程序
path=$(dirname $(readlink -f $0))

if [ $# -eq 0 ]; then
    # 未指定配置名，发布全部
    files=($(ls -la $path/.publish.config.* | grep -v 'example' | grep -v 'test' | awk {'print $9'}))
    for configFile in "${files[@]}"; do
        _build $path ${configFile#*.publish.config.}
    done
else
    # 发布指定配置名
    for i in "$@"; do
        _build $path $i
    done
fi

# 计时
end=$(date "+%s")
time=$((end - start))

# 完成
dateTime=$(date "+%Y-%m-%d %H:%M:%S")
echo -e "\n$dateTime 构建完成，耗时 $time 秒."
