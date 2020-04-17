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
# 指定 token 的有效时间（单位秒），默认 24 小时（60*60*24=86400）
FLY_JWT_TTL=86400
# 指定允许跨域请求的站点，多个站点用 | 分隔
FLY_CORS_ALLOW_ORIGIN=http://localhost
# 指定用户模型类，实现 RBAC 的关键配置
FLY_USER_MODEL=\App\Models\User
# 记录 SQL 日志，默认 false
FLY_SQL_LOG=false
```

2. 手动添加日志迁移到文件的任务

修改 laravel 工程 app\Console\Kernel.php 文件，在 schedule 函数中增加
```php
// 迁移日志到文件
$schedule
    ->call(function () {
        try {
            \mradang\LaravelFly\Services\LogService::migrateToFile();
        } catch (\Exception $e) {
            logger()->warning(sprintf('Kernel.schedule 迁移日志到文件失败：%s', $e->getMessage()));
        }
    })
    ->cron('0 0 2 * *')
    ->name('LogService::migrateToFile')
    ->withoutOverlapping();
```

3. 刷新数据库迁移
```bash
php artisan migrate:refresh
```

## 添加的内容

### 添加的数据表迁移
- rbac_access
- rbac_node
- rbac_role_user
- rbac_role
- logs
- options

### 添加的路由
- post /fly/rbac/allNodes
- post /fly/rbac/allNodesWithRole
- post /fly/rbac/refreshNodes
- post /fly/rbac/allRoles
- post /fly/rbac/createRole
- post /fly/rbac/findRoleWithNodes
- post /fly/rbac/syncNodeRoles
- post /fly/rbac/syncRoleNodes
- post /fly/rbac/updateRole
- post /fly/rbac/deleteRole
- post /fly/rbac/saveRoleSort
- post /fly/log/lists

### 添加的路由中间件
二选一即可
1. auth 需要授权的路由
2. auth.basic 只需登录的路由

### 添加的助手函数
1. 数据库日志，用于记录用户操作
```php
void L($msg, $username = null)
```

2. 调试函数，使用 LOG 类输出 debug 级别日志
```php
void debug(mixed $value1[, mixed $value2[, mixed $...]])
```

3. 生成模型更改信息，模型数据变更但未保存时调用
```php
string change_log($model)
```

4. key/value 读写
```php
mixed option(string $key[, mixed $value])
```

### 添加的命令
1. 生成路由描述文件：storage/app/route_desc.json
```bash
php artisan fly:MakeRouteDescFile
```

2. 刷新路由节点及描述
```bash
php artisan fly:RefreshRbacNode
```

## 用户认证功能

### 基础配置
user 数据表必须包含字段：id, name, secret
```php
$table->increments('id');
$table->string('name');
$table->string('secret')->nullable();
```

### 模型 Trait
```php
use mradang\LaravelFly\Traits\UserModelTrait;
```

增加以下内容：
> - belongsToMany rbacRoles 角色关联（多对多）
> - array getAccessAttribute 权限属性 access，user 模型需实现 getIsAdminAttribute（超级管理员）属性
> - string rbacMakeToken(array $fields = ['id', 'name']) 生成用户访问令牌
> - bool rbacResetSecret() 重置用户安全码
> - void rbacSyncRoles(array $roles) 同步用户与角色的关联，$roles 为角色 id 数组
> - void rbacDeleteUser() 删除用户权限信息
