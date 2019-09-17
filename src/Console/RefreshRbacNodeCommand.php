<?php

namespace mradang\LaravelFly\Console;

use Illuminate\Console\Command;
use mradang\LaravelFly\Services\RbacNodeService;
use Illuminate\Support\Facades\DB;

class RefreshRbacNodeCommand extends Command {

    protected $signature = 'fly:RefreshRbacNode';

    protected $description = 'Refresh the routing node and read the comment file';

    public function handle() {
        $ready = false;
        try {
            $ready = !empty(DB::select('DESCRIBE rbac_node'));
        } catch (\Exception $e) {
            $ready = false;
        }

        if ($ready) {
            RbacNodeService::refresh();
        } else {
            info('数据库表不存在，未能刷新 RBAC 节点。');
        }
    }

}