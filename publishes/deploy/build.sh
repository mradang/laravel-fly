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

    cd $path/../docker

    # 配置文件
    cp .env.example .env
    sed -i "s|COMPOSE_PROJECT_NAME=.*|COMPOSE_PROJECT_NAME=${KEY}|" .env
    sed -i "s|NGINX_PORT=.*|NGINX_PORT=${NGINX_PORT}|" .env
    sed -i "s|MYSQL_PORT=.*|MYSQL_PORT=${MYSQL_PORT}|" .env
    PASSWORD=$(printf '%s\n' "$MYSQL_ROOT_PASSWORD" | sed -e 's/[\/&]/\\&/g')
    sed -i "s|MYSQL_ROOT_PASSWORD=.*|MYSQL_ROOT_PASSWORD=${PASSWORD}|" .env
    sed -i "s|MYSQL_DATA_VOLUME=.*|MYSQL_DATA_VOLUME=/volumes/${KEY}/mysql_data|" .env
    sed -i "s|REDIS_DATA_VOLUME=.*|REDIS_DATA_VOLUME=/volumes/${KEY}/redis_data|" .env
    sed -i "s|CODE_VOLUME=.*|CODE_VOLUME=/var/www/${KEY}/serve|" .env

    # 复制 docker 编排文件到宿主机
    docker_dir=/docker/$KEY
    ssh -p $PORT $USER@$HOST "mkdir -p $docker_dir"
    scp -P $PORT -r ./ $USER@$HOST:$docker_dir

    # 构建镜像
    docker build -t $KEY-nginx ./nginx
    docker build -t $KEY-php ./php
    docker image prune -f

    # 导出镜像
    docker save $KEY-nginx | gzip >/tmp/$KEY-nginx.tar.gz
    docker save $KEY-php | gzip >/tmp/$KEY-php.tar.gz

    # 发布镜像到宿主机
    ssh -p $PORT $USER@$HOST "rm /tmp/$KEY-nginx.tar.gz -f"
    ssh -p $PORT $USER@$HOST "rm /tmp/$KEY-php.tar.gz -f"
    scp -P $PORT /tmp/$KEY-nginx.tar.gz $USER@$HOST:/tmp
    scp -P $PORT /tmp/$KEY-php.tar.gz $USER@$HOST:/tmp

    ssh -p $PORT $USER@$HOST "docker load -i /tmp/$KEY-nginx.tar.gz"
    ssh -p $PORT $USER@$HOST "docker load -i /tmp/$KEY-php.tar.gz"
    ssh -p $PORT $USER@$HOST "docker image prune -f"

    # 启动
    ssh -p $PORT $USER@$HOST "cd $docker_dir; docker-compose stop; docker-compose up -d"

    # mysql容器
    mycnf=/tmp/$KEY.my.cnf
    echo '[client]' >$mycnf
    echo 'user=root' >>$mycnf
    echo "password=${MYSQL_ROOT_PASSWORD}" >>$mycnf
    echo '[mysqldump]' >>$mycnf
    echo 'user=root' >>$mycnf
    echo "password=${MYSQL_ROOT_PASSWORD}" >>$mycnf
    scp -P $PORT $mycnf $USER@$HOST:/tmp/$KEY.my.cnf
    ssh -p $PORT $USER@$HOST "cd $docker_dir; docker-compose cp /tmp/$KEY.my.cnf mysql:/root/.my.cnf"
    ssh -p $PORT $USER@$HOST "cd $docker_dir; docker-compose exec mysql chmod 600 /root/.my.cnf"
    ssh -p $PORT $USER@$HOST "rm /tmp/$KEY.my.cnf -f"

    # 清理
    rm $path/../docker/.env /tmp/$KEY.my.cnf /tmp/$KEY-nginx.tar.gz /tmp/$KEY-php.tar.gz -rf
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
    files=($(ls -la $path/.publish.config.* | grep -v 'example' | awk {'print $9'}))
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
