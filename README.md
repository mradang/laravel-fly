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

1. 修改 app\Exceptions\Handler.php 文件
```php
protected $dontReport = [
    // ......
    \App\Services\Exception::class,
];

public function render($request, Exception $exception)
{
    // 将App异常改为http400错误输出
    if ($exception instanceof \App\Services\Exception) {
        return response($exception->getMessage(), 400);
    }
    return parent::render($request, $exception);
}
```