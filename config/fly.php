<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Token 有效期
    |--------------------------------------------------------------------------
    |
    | 指定 token 的有效时间（单位秒），默认 1 天（60*60*24=86400）
    |
    */
    'ttl' => env('FLY_JWT_TTL', 86400),

    /*
    |--------------------------------------------------------------------------
    | 跨域请求源地址
    |--------------------------------------------------------------------------
    |
    | 指定允许跨域请求的站点，多个站点用 | 分隔
    |
    */
    'cors' => env('FLY_CORS_ALLOW_ORIGIN', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | 用户模型
    |--------------------------------------------------------------------------
    |
    | 指定用户模型类，实现 RBAC 的关键配置
    |
    */
    'user_model' => env('FLY_USER_MODEL', \App\Models\User::class),

    /*
    |--------------------------------------------------------------------------
    | SQL 日志
    |--------------------------------------------------------------------------
    |
    | 是否记录 SQL 日志
    |
    */
    'sql_log' => env('FLY_SQL_LOG', false),

    /*
    |--------------------------------------------------------------------------
    | 可信代理
    |--------------------------------------------------------------------------
    |
    | 反向代理地址（CIDR），多个地址使用 | 分隔
    |
    */
    'trusted_proxies' => env('FLY_TRUSTED_PROXIES', ''),

    /*
    |--------------------------------------------------------------------------
    | 附件存储在 storage 下的目录名（默认：attachments）
    |--------------------------------------------------------------------------
    */
    'attachment_folder' => env('ATTACHMENT_FOLDER', 'attachments'),

    /*
    |--------------------------------------------------------------------------
    | 缩略图存储在 storage 下的目录名（默认：thumbs）
    |--------------------------------------------------------------------------
    */
    'thumb_folder' => env('ATTACHMENT_THUMB_FOLDER', 'thumbs'),

];
