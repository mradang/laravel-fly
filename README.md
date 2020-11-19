## 安装

```shell
$ composer require mradang/laravel-fly -vvv
```

### 可选项

1. 发布配置文件、快捷脚本和运维脚本

```shell
$ php artisan vendor:publish --provider="mradang\\LaravelFly\\LaravelFlyServiceProvider"
```

2. 创建队列表迁移
```shell
$ php artisan queue:table
```

## 配置

1. 添加 .env 环境变量，使用默认值时可省略
```
# 记录 SQL 日志，默认 false
FLY_SQL_LOG=false
```

## 添加的内容

### 添加的助手函数
1. 调试函数，使用 LOG 类输出 debug 级别日志
```php
void debug(mixed $value1[, mixed $value2[, mixed $...]])
```

2. 生成模型更改信息，模型数据变更但未保存时调用
```php
string change_log($model)
```
