<?php

namespace mradang\LaravelFly\Console;

use Illuminate\Console\Command;
use mradang\LaravelFly\Services\RbacNodeService;

class MakeRouteDescFileCommand extends Command
{
    protected $signature = 'fly:MakeRouteDescFile';

    protected $description = 'Generate route description file';

    public function handle()
    {
        $n = RbacNodeService::makeRouteDescFile();
        if ($n) {
            echo '成功生成路由描述文件';
        } else {
            echo '生成路由描述文件失败';
        }
    }
}
