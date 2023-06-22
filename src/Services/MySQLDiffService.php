<?php

namespace mradang\LaravelFly\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PDO;

class MySQLDiffService
{
    public static function getStruct(PDO $pdo): array
    {
        return [
            'tables' => self::getTables($pdo),
            'columns' => self::getColumns($pdo),
            'indexes' => self::getIndexes($pdo),
        ];
    }

    public static function diff(array $arr1, array $arr2, array $primaryKeys): array
    {
        $key1 = array_map(function ($value) use ($primaryKeys) {
            return implode(',', Arr::only($value, $primaryKeys));
        }, $arr1);
        $value1 = array_map(function ($value) {
            return implode(',', $value);
        }, $arr1);
        $arr1 = array_combine($key1, $value1);

        $key2 = array_map(function ($value) use ($primaryKeys) {
            return implode(',', Arr::only($value, $primaryKeys));
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

    private static function getTables(PDO $pdo): array
    {
        $sql = 'show tables';
        $stmt = $pdo->query($sql);
        $stmt->setFetchMode(PDO::FETCH_NUM);

        return $stmt->fetchAll();
    }

    private static function getColumns(PDO $pdo): array
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
        $sql = 'SELECT '.implode(',', $fields);
        $sql .= ' from information_schema.COLUMNS where TABLE_SCHEMA=?';
        $params = [$pdo->query('select database()')->fetchColumn()];

        $sth = $pdo->prepare($sql);
        $sth->execute($params);
        $cols = $sth->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($col) {
            if ($col['COLUMN_DEFAULT'] === 'NULL') {
                $col['COLUMN_DEFAULT'] = null;
            }
            if ($col['COLUMN_DEFAULT'] === 'current_timestamp()') {
                $col['COLUMN_DEFAULT'] = 'CURRENT_TIMESTAMP';
            }
            // 忽略整型字段长度
            if (Str::startsWith($col['COLUMN_TYPE'], ['int', 'tinyint', 'bigint'])) {
                $col['COLUMN_TYPE'] = preg_replace('/\(\d+\)/', '', $col['COLUMN_TYPE']);
            }
            // MySQL 8 在 column 上有表达式类型的默认值时，会在该行的 Extra 列打上 DEFAULT_GENERATED 的 tag
            if ($col['EXTRA'] === 'DEFAULT_GENERATED') {
                $col['EXTRA'] = '';
            }

            return $col;
        }, $cols);
    }

    private static function getIndexes(PDO $pdo): array
    {
        // 表名，唯一索引，索引名，列序号，列名
        $fields = [
            'TABLE_NAME',
            'NON_UNIQUE',
            'INDEX_NAME',
            'SEQ_IN_INDEX',
            'COLUMN_NAME',
        ];
        $sql = 'SELECT '.implode(',', $fields);
        $sql .= ' from information_schema.STATISTICS where TABLE_SCHEMA=?';
        $params = [$pdo->query('select database()')->fetchColumn()];

        $sth = $pdo->prepare($sql);
        $sth->execute($params);
        $cols = $sth->fetchAll(PDO::FETCH_ASSOC);

        return $cols;
    }
}
