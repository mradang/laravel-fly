## 安装

```shell
composer require mradang/laravel-fly -vvv
```

### 可选项

1. 发布脚本文件和 docker 文件

```shell
php artisan vendor:publish --provider="mradang\\LaravelFly\\LaravelFlyServiceProvider"
```

## 添加的内容

### 添加的助手函数

1. 调试函数，使用 LOG 类输出 debug 级别日志

```php
void debug(mixed $value1[, mixed $value2[, mixed $...]])
```
