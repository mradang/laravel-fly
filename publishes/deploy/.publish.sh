#!/bin/bash

# 调用方法
# .publish.sh $configFile

# 常量参数
path=$(dirname $(readlink -f $0))
project=$(basename $(dirname $path))
# 读取环境变量
if [ -e $path/.env ]; then
  source $path/.env
fi
# 读取发布配置
source $1

# 克隆项目
if [ -d /tmp/$project -a -e /tmp/$project/config ]; then
  cd /tmp/$project
  git fetch --all
  git reset --hard origin/master
else
  rm /tmp/$project -rf
  remote_url=$(git remote get-url origin)
  git clone --depth=1 $remote_url /tmp/$project
fi

# 代码版本
v=$(git rev-parse --short HEAD)

# 打包
if [ ! -e /tmp/$project.$v.tar.gz ]; then
  if [ -n $HTTP_PROXY ]; then
    export http_proxy=$HTTP_PROXY
  fi

  cd /tmp/$project
  composer --no-dev install

  if [ -s /tmp/$project/vendor/autoload.php ]; then
    tar -czf /tmp/$project.$v.tar.gz -C/tmp/$project/ .
  else
    echo "$project 打包失败."
    exit
  fi
fi

# 执行发布
ssh -p $PORT root@$HOST "rm /tmp/$project.$v* -rf"
scp -P $PORT /tmp/$project.$v.tar.gz root@$HOST:/tmp
ssh -p $PORT root@$HOST "mkdir /tmp/$project.$v"
ssh -p $PORT root@$HOST "tar -xzf /tmp/$project.$v.tar.gz -C /tmp/$project.$v"

ssh -p $PORT root@$HOST "cd /var/www/$DIR/; rm config/ -rf; rm app/ -rf; rm vendor/ -rf;"
ssh -p $PORT root@$HOST "\cp /tmp/$project.$v/* /var/www/$DIR/ -a"

ssh -p $PORT root@$HOST "restorecon -RF /var/www/$DIR/"
ssh -p $PORT root@$HOST "chown apache:apache /var/www/$DIR/* -R"
ssh -p $PORT root@$HOST "chmod a+rw /var/www/$DIR/storage -R"
ssh -p $PORT root@$HOST "chcon -t httpd_sys_rw_content_t /var/www/$DIR/storage -R >> /dev/null 2>&1"

ssh -p $PORT root@$HOST "cd /var/www/$DIR/; php artisan route:cache"
ssh -p $PORT root@$HOST "cd /var/www/$DIR/; php artisan rbac:RefreshRbacNode"

ssh -p $PORT root@$HOST "systemctl restart supervisord"
