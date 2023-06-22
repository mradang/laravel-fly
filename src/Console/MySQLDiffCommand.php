<?php

namespace mradang\LaravelFly\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use mradang\LaravelFly\Services\MySQLDiffService;

class MySQLDiffCommand extends Command
{
    protected $signature = 'fly:mysqldiff {--baseStructFile=}';

    protected $description = 'mysqldiff';

    public function handle()
    {
        $pdo = DB::connection()->getPdo();
        $myStruct = MySQLDiffService::getStruct($pdo);

        $baseStruct = json_decode(file_get_contents($this->option('baseStructFile')), true);

        // 检查表
        $this->line('');
        $this->question('检查数据表：');
        $diff = MySQLDiffService::diff(
            $myStruct['tables'],
            $baseStruct['tables'],
            [0],
        );
        if (count($diff) === 0) {
            $this->info('数据表无差异！');
        } else {
            $this->error('数据表有差异：');
            $this->table(['Current', 'Base'], $diff);
        }

        // 检查字段
        $this->line('');
        $this->question('检查表字段：');
        $diff = MySQLDiffService::diff(
            $myStruct['columns'],
            $baseStruct['columns'],
            ['TABLE_NAME', 'COLUMN_NAME'],
        );
        if (count($diff) === 0) {
            $this->info('表字段无差异！');
        } else {
            $this->error('表字段有差异：');
            $this->comment('表名，列名，默认值，是否为空，字符集，字符序，类型，属性，扩展');
            $this->table(['Current', 'Base'], $diff);
        }

        // 检查索引
        $this->line('');
        $this->question('检查表索引：');
        $diff = MySQLDiffService::diff(
            $myStruct['indexes'],
            $baseStruct['indexes'],
            ['TABLE_NAME', 'INDEX_NAME', 'SEQ_IN_INDEX'],
        );
        if (count($diff) === 0) {
            $this->info('表索引无差异！');
        } else {
            $this->error('表索引有差异：');
            $this->comment('表名，唯一索引，索引名，列序号，列名');
            $this->table(['Current', 'Base'], $diff);
        }
    }
}
