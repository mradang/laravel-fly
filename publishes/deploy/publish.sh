#!/bin/bash

# 调用方法
# publish.sh [config_name config_name ...]

_publish() {
    # 常量参数
    path=$1
    configName=$2
    project=$(basename $(dirname $path))

    # 读取发布配置
    configFile=$path/.publish.config.$configName
    if [ ! -e $configFile ]; then
        echo "未找到发布配置文件$configFile"
        return 1
    fi
    source $configFile

    # 克隆项目
    rm /tmp/$project -rf
    git clone --depth=1 $path/../ /tmp/$project
    cd /tmp/$project

    # 代码版本
    v=$(git rev-parse --short HEAD)

    # 打包
    if [ ! -e /tmp/$project.$v.tar.gz ]; then
        docker run --rm -u www-data -v "/tmp/$project:/var/www/html" -w /var/www/html $KEY-php:latest composer --no-dev install

        if [ -s /tmp/$project/vendor/autoload.php ]; then
            tar -czf /tmp/$project.$v.tar.gz \
                --exclude .git --exclude deploy --exclude docker --exclude .vscode \
                .
        else
            echo "$project 打包失败."
            return 1
        fi
    fi

    # 上传宿主机
    local_size=$(ls -l /tmp/$project.$v.tar.gz | awk '{print $5}')
    server_size=$(ssh -p $PORT $USER@$HOST "ls -l /tmp/$project.$v.tar.gz 2>/dev/null | awk '{print \$5}'")
    if [ ${server_size:-0} -ne $local_size ]; then
        scp -P $PORT /tmp/$project.$v.tar.gz $USER@$HOST:/tmp
    fi
    ssh -p $PORT $USER@$HOST "mkdir /tmp/$project.$v"
    ssh -p $PORT $USER@$HOST "tar -mxzf /tmp/$project.$v.tar.gz -C /tmp/$project.$v"

    # 执行发布
    publish_dir=/home/$USER/$KEY/www/serve
    ssh -p $PORT $USER@$HOST "rm $publish_dir/config/ $publish_dir/app/ $publish_dir/vendor/ -rf"
    ssh -p $PORT $USER@$HOST "\cp /tmp/$project.$v/* $publish_dir/ -a"
    ssh -p $PORT $USER@$HOST "rm /tmp/$project.$v -rf"

    # 发布配置文件
    cp $path/../.env.example /tmp/$KEY.env

    sed -i "s|APP_NAME=.*|APP_NAME=${KEY}|" /tmp/$KEY.env
    sed -i "s|APP_ENV=.*|APP_ENV=production|" /tmp/$KEY.env
    sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|" /tmp/$KEY.env
    sed -i "s|APP_URL=.*|APP_URL=${APP_URL}|" /tmp/$KEY.env
    sed -i "s|LOG_CHANNEL=.*|LOG_CHANNEL=daily|" /tmp/$KEY.env
    sed -i "s|DB_CONNECTION=.*|DB_CONNECTION=mysql|" /tmp/$KEY.env
    sed -i "s|# DB_HOST=.*|DB_HOST=mysql|" /tmp/$KEY.env
    sed -i "s|# DB_PORT=.*|DB_PORT=3306|" /tmp/$KEY.env
    sed -i "s|# DB_DATABASE=.*|DB_DATABASE=app|" /tmp/$KEY.env
    sed -i "s|# DB_USERNAME=.*|DB_USERNAME=root|" /tmp/$KEY.env
    DB_PASSWORD=$(printf '%s\n' "$MYSQL_ROOT_PASSWORD" | sed -e 's/[\/&]/\\&/g')
    sed -i "s|# DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" /tmp/$KEY.env

    echo -e '\n# --------------------------------\n' >>/tmp/$KEY.env
    cat $path/app.env.$configName >>/tmp/$KEY.env
    scp -P $PORT /tmp/$KEY.env $USER@$HOST:$publish_dir/.env
    rm /tmp/$KEY.env -f

    # 操作容器
    docker_exec="cd /home/$USER/$KEY/www/serve/docker; docker compose exec"

    # php容器
    ssh -p $PORT $USER@$HOST "$docker_exec -u www-data php /usr/local/bin/after_publish.sh"
    ssh -p $PORT $USER@$HOST "$docker_exec php /usr/bin/supervisorctl reload"

    echo "$configName 已发布"
}

# 倒计时
for i in $(seq 5 | tac); do
    echo -en $i"秒后开始发布...\r"
    sleep 1
done
start=$(date "+%s")

# 主程序
path=$(dirname $(readlink -f $0))

if [ $# -eq 0 ]; then
    # 未指定配置名，发布全部
    files=($(ls -la $path/.publish.config.* | grep -v 'example' | grep -v 'test' | awk {'print $9'}))
    for configFile in "${files[@]}"; do
        _publish $path ${configFile#*.publish.config.}
    done
else
    # 发布指定配置名
    for i in "$@"; do
        _publish $path $i
    done
fi

# 计时
end=$(date "+%s")
time=$((end - start))

# 完成
dateTime=$(date "+%Y-%m-%d %H:%M:%S")
echo -e "\n$dateTime 发布完成，耗时 $time 秒."
