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
    if [ -d /tmp/$project -a -e /tmp/$project/.git/config ]; then
        cd /tmp/$project
        git fetch --all
        git reset --hard origin/master
    else
        rm /tmp/$project -rf
        remote_url=$(git remote get-url origin)
        git clone --depth=1 $remote_url /tmp/$project
        cd /tmp/$project
    fi

    # 代码版本
    v=$(git rev-parse --short HEAD)

    # 打包
    if [ ! -e /tmp/$project.$v.tar.gz ]; then
        composer --no-dev install

        if [ -s /tmp/$project/vendor/autoload.php ]; then
            tar -czf /tmp/$project.$v.tar.gz -C/tmp/$project/ . --exclude .git deploy docker
        else
            echo "$project 打包失败."
            return 1
        fi
    fi

    # 上传宿主机
    scp -P $PORT /tmp/$project.$v.tar.gz $USER@$HOST:/tmp
    ssh -p $PORT $USER@$HOST "mkdir /tmp/$project.$v"
    ssh -p $PORT $USER@$HOST "tar -mxzf /tmp/$project.$v.tar.gz -C /tmp/$project.$v"

    # 执行发布
    publish_dir=/var/www/$KEY/serve
    ssh -p $PORT $USER@$HOST "mkdir -p $publish_dir"
    ssh -p $PORT $USER@$HOST "rm $publish_dir/config/ $publish_dir/app/ $publish_dir/vendor/ -rf"
    ssh -p $PORT $USER@$HOST "\cp /tmp/$project.$v/* $publish_dir/ -a"
    ssh -p $PORT $USER@$HOST "cd $publish_dir; chmod a+rw storage -R"
    ssh -p $PORT $USER@$HOST "rm /tmp/$project.$v* -rf"

    # 发布配置文件
    cp $path/app.env.$configName /tmp/$KEY.env
    sed -i "s|APP_NAME=.*|APP_NAME=${KEY}|" /tmp/$KEY.env
    DB_PASSWORD=$(printf '%s\n' "$MYSQL_ROOT_PASSWORD" | sed -e 's/[\/&]/\\&/g')
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" /tmp/$KEY.env
    scp -P $PORT /tmp/$KEY.env $USER@$HOST:$publish_dir/.env
    rm /tmp/$KEY.env -f

    # 操作容器
    docker_exec="cd /docker/$KEY; docker-compose exec"

    # php容器
    artisan="cd /var/www/html; /usr/local/bin/php artisan"
    ssh -p $PORT $USER@$HOST "$docker_exec php sh -c '$artisan key:generate --force'"
    ssh -p $PORT $USER@$HOST "$docker_exec php sh -c '$artisan config:cache'"
    ssh -p $PORT $USER@$HOST "$docker_exec php sh -c '$artisan route:cache'"
    ssh -p $PORT $USER@$HOST "$docker_exec php sh -c '$artisan event:cache'"
    ssh -p $PORT $USER@$HOST "$docker_exec php sh -c '$artisan rbac:RefreshRbacNode'"
    ssh -p $PORT $USER@$HOST "$docker_exec php /usr/bin/supervisorctl reload"
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
    files=($(ls -la $path/.publish.config.* | grep -v 'example' | awk {'print $9'}))
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
