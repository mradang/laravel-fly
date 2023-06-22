<?php

namespace mradang\LaravelFly\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use mradang\LaravelFly\Services\MySQLDiffService;

class MySQLStructCommand extends Command
{
    protected $signature = 'fly:mysqlstruct';

    protected $description = 'mysqlstruct';

    public function handle()
    {
        $pdo = DB::connection()->getPdo();
        $struct = MySQLDiffService::getStruct($pdo);

        echo json_encode($struct);
    }
}
