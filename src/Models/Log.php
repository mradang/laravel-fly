<?php

namespace mradang\LaravelFly\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        // 'id',
        'username',
        'ip',
        'log_msg',
    ];
}
