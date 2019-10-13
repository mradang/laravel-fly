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
# 指定 LaravelFly 的 route 路径前缀，默认 api/fly（http://hostname/api/fly/路由）
FLY_ROUTE_URI=api/fly
# 指定允许跨域请求的站点，多个站点用 | 分隔
FLY_CORS_ALLOW_ORIGIN=http://localhost
# 指定用户模型类，实现 RBAC 的关键配置
FLY_USER_MODEL=\App\Models\User
# 记录 SQL 日志，默认 false
FLY_SQL_LOG=false
# 附件存储在 storage 下的目录名（默认：attachments）
ATTACHMENT_FOLDER=attachments
# 缩略图存储在 storage 下的目录名（默认：thumbs）
ATTACHMENT_THUMB_FOLDER=thumbs
```

2. 修改 app\Exceptions\Handler.php 文件
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

3. 手动添加日志迁移到文件的任务

修改 laravel 工程 app\Console\Kernel.php 文件，在 schedule 函数中增加
```php
try {
    $schedule
    ->call(function () {
        \mradang\LaravelFly\Services\LogService::migrateToFile();
    })
    ->cron('0 0 2 * *')
    ->name('LogService::migrateToFile')
    ->withoutOverlapping()
    ->after(function () {
        L('Kernel.schedule 迁移日志到文件成功', 'sys');
    });
} catch (\Exception $e) {
    L(sprintf('Kernel.schedule 迁移日志到文件失败：%s', $e->getMessage()), 'sys');
}
```

4. 刷新数据库迁移
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
- attachments
- custom_field_groups
- custom_fields
- custom_field_values

### 添加的路由
- post /fly/rbac/allNodes
- post /fly/rbac/allNodesWithRole
- post /fly/rbac/refreshNodes
- post /fly/rbac/allRoles
- post /fly/rbac/createRole
- post /fly/rbac/findRoleWithNodes
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

## 模型附件功能

### 模型 Trait

```php
use mradang\LaravelFly\Traits\AttachmentTrait;
```

增加以下内容：
> - morphMany attachments 附件关联（一对多）
> - mradang\LaravelFly\Models\Attachment attachmentAddByFile($file, array $data = []) 为模型上传文件附件
> - mradang\LaravelFly\Models\Attachment attachmentAddByUrl($url, array $data = []) 为模型上传 Url 附件
> - void attachmentDelete($id) 删除模型的指定附件
> - void attachmentClear() 清空模型的全部附件
> - response attachmentDownload($id, $name = '') 下载指定附件
> - response attachmentShowPic($id, $width = 0, $height = 0) 显示指定附件图片
> - mradang\LaravelFly\Models\Attachment attachmentFind($id) 查找指定附件

## 定制字段功能

### 控制器 Trait
```php
use mradang\LaravelFly\Traits\CustomFieldControllerTrait;
```

增加以下内容：

- 获取定制模型类
```php
abstract protected function customFieldModel();
```

- 保留字段分组名（array）
```php
protected function customFieldExcludeGroups();
```

- 保留字段名（array）
```php
protected function customFieldExcludeFields();
```

- 保存字段分组
```php
saveFieldGroup(Request $request)
[id, name]
```

- 获取字段分组
```php
getFieldGroups()
```

- 删除字段分组
```php
deleteFieldGroup(Request $request)
[id]
```

- 字段分组排序
```php
sortFieldGroups(Request $request)
[{id, sort}]
```

- 保存字段
```php
saveField(Request $request)
[id, name, type, options, group_id]
```

- 获取字段
```php
getFields()
```

- 删除字段
```php
deleteField(Request $request)
[id]
```

- 字段排序
```php
sortFields(Request $request)
[{id, sort}]
```

- 字段移动
```php
moveField(Request $request)
[id, group_id]
```

### 模型 Trait
```php
use mradang\LaravelFly\Traits\CustomFieldTrait;
```

增加以下内容
- 获取字段分组
```php
Model::customFieldGroups()
```

- 创建字段分组
```php
Model::customFieldGroupCreate($name)
```

- 确保字段分组存在
```php
Model::customFieldGroupEnsureExists($name)
```

- 更新字段分组
```php
Model::customFieldGroupUpdate($id, $name)
```

- 删除字段分组
```php
Model::customFieldGroupDelete($id)
```

- 字段分组排序
```php
Model::customFieldGroupSaveSort(array $data)
```

- 获取字段
```php
Model::customFields()
```

- 按分组获取字段
```php
Model::customFieldsByGroupId($id, $name)
```

- 创建字段
```php
Model::customFieldCreate($name, $type, array $options = [], $group_id = 0)
```

- 修改字段
```php
Model::customFieldUpdate($id, $name, $type, array $options = [], $group_id = 0)
```

- 删除字段
```php
Model::customFieldDelete($id)
```

- 字段排序
```php
Model::customFieldSaveSort(array $data)
```

- 字段移动
```php
Model::customFieldMove($id, $group_id)
```

- 保存定制字段数据
```php
$model->customFieldSaveData(array $data)
```

- 保存单个定制字段数据
```php
$model->customFieldSaveDataItem(int $field_id, $value)
```

- 获取单个定制字段数据
```php
$model->customFieldGetDataItem(int $field_id)
```

- 取定制字段数据
```php
array $model->customFieldData
```

- 清理字段值
```php
$model->customFieldClearValues()
```

### 异常
- mradang\LaravelFly\Exceptions\CustomFieldException


### 根据需要增加路由
```php
Route::post('getFieldGroups', 'XXXXController@getFieldGroups');
Route::post('getFields', 'XXXXController@getFields');

Route::post('saveFieldGroup', 'XXXXController@saveFieldGroup');
Route::post('deleteFieldGroup', 'XXXXController@deleteFieldGroup');
Route::post('sortFieldGroups', 'XXXXController@sortFieldGroups');
Route::post('saveField', 'XXXXController@saveField');
Route::post('deleteField', 'XXXXController@deleteField');
Route::post('sortFields', 'XXXXController@sortFields');
Route::post('moveField', 'XXXXController@moveField');
```