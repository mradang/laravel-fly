<?php

namespace mradang\LaravelFly\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class MySQLDiffCommand extends Command
{
    protected $signature = 'fly:mysqldiff {--host1=} {--dbname1=} {--auth1=} {--host2=} {--dbname2=} {--auth2=}';

    protected $description = 'mysqldiff';

    public function handle()
    {
        $pdo1 = $this->getPDO(1);
        $pdo2 = $this->getPDO(2);

        // 检查表
        $this->line('');
        $this->question('检查数据表：');
        $diff = $this->diff($this->getTables($pdo1), $this->getTables($pdo2), [0]);
        if (count($diff) === 0) {
            $this->info('数据表无差异！');
        } else {
            $this->error('数据表有差异：');
            $this->table(['DSN1', 'DSN2'], $diff);
        }

        // 检查字段
        $this->line('');
        $this->question('检查表字段：');
        $diff = $this->diff($this->getColumns($pdo1), $this->getColumns($pdo2), ['TABLE_NAME', 'COLUMN_NAME']);
        if (count($diff) === 0) {
            $this->info('表字段无差异！');
        } else {
            $this->error('表字段有差异：');
            $this->comment('表名，列名，默认值，是否为空，字符集，字符序，类型，属性，扩展');
            $this->table(['DSN1', 'DSN2'], $diff);
        }

        // 检查索引
        $this->line('');
        $this->question('检查表索引：');
        $diff = $this->diff($this->getIndexes($pdo1), $this->getIndexes($pdo2), ['NON_UNIQUE', 'INDEX_NAME']);
        if (count($diff) === 0) {
            $this->info('表索引无差异！');
        } else {
            $this->error('表索引有差异：');
            $this->comment('表名，唯一索引，索引名，列序号，列名');
            $this->table(['DSN1', 'DSN2'], $diff);
        }
    }

    private function getPDO($index)
    {
        list($host, $port) = explode(':', $this->option('host' . $index));
        $dbname = $this->option('dbname' . $index);
        list($username, $password) = explode(':', $this->option('auth' . $index));
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s', $host, $port, $dbname);
        return new \PDO($dsn, $username, $password);
    }

    private function diff($arr1, $arr2, array $pri)
    {
        $key1 = array_map(function ($value) use ($pri) {
            return implode(',', Arr::only($value, $pri));
        }, $arr1);
        $value1 = array_map(function ($value) {
            return implode(',', $value);
        }, $arr1);
        $arr1 = array_combine($key1, $value1);

        $key2 = array_map(function ($value) use ($pri) {
            return implode(',', Arr::only($value, $pri));
        }, $arr2);
        $value2 = array_map(function ($value) {
            return implode(',', $value);
        }, $arr2);
        $arr2 = array_combine($key2, $value2);

        $keys = array_values(array_unique(array_merge($key1, $key2)));
        $diff = [];
        foreach ($keys as $key) {
            $value = [
                Arr::get($arr1, $key),
                Arr::get($arr2, $key),
            ];
            if ($value[0] !== $value[1]) {
                $diff[] = $value;
            }
        }
        return $diff;
    }

    private function getTables($pdo)
    {
        $sql = 'show tables';
        $stmt = $pdo->query($sql);
        $stmt->setFetchMode(\PDO::FETCH_NUM);
        return $stmt->fetchAll();
    }

    private function getColumns($pdo)
    {
        // 表名，列名，默认值，是否为空，字符集，字符序，类型，属性，扩展
        $fields = [
            'TABLE_NAME',
            'COLUMN_NAME',
            'COLUMN_DEFAULT',
            'IS_NULLABLE',
            'CHARACTER_SET_NAME',
            'COLLATION_NAME',
            'COLUMN_TYPE',
            'COLUMN_KEY',
            'EXTRA',
        ];
        $sql = 'SELECT ' . implode(',', $fields);
        $sql .= ' from information_schema.COLUMNS where TABLE_SCHEMA=?';
        $params = [$pdo->query('select database()')->fetchColumn()];

        $sth = $pdo->prepare($sql);
        $sth->execute($params);
        $cols = $sth->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(function ($col) {
            if ($col['COLUMN_DEFAULT'] === 'NULL') {
                $col['COLUMN_DEFAULT'] = null;
            }
            if ($col['COLUMN_DEFAULT'] === 'current_timestamp()') {
                $col['COLUMN_DEFAULT'] = 'CURRENT_TIMESTAMP';
            }
            return $col;
        }, $cols);
    }

    private function getIndexes($pdo)
    {
        // 表名，唯一索引，索引名，列序号，列名
        $fields = [
            'TABLE_NAME',
            'NON_UNIQUE',
            'INDEX_NAME',
            'SEQ_IN_INDEX',
            'COLUMN_NAME',
        ];
        $sql = 'SELECT ' . implode(',', $fields);
        $sql .= ' from information_schema.STATISTICS where TABLE_SCHEMA=?';
        $params = [$pdo->query('select database()')->fetchColumn()];

        $sth = $pdo->prepare($sql);
        $sth->execute($params);
        $cols = $sth->fetchAll(\PDO::FETCH_ASSOC);

        return $cols;
    }
}
