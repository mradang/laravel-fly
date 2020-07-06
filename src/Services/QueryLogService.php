<?php

namespace mradang\LaravelFly\Services;

// https://github.com/overtrue/laravel-query-logger

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryLogService
{
    public static function log()
    {
        $request = app()->request;

        if ($request->method() === 'OPTIONS') {
            return;
        }

        Log::info(sprintf('------ %s: %s ------', $request->method(), $request->fullUrl()));

        DB::listen(function (QueryExecuted $query) {
            $sqlWithPlaceholders = str_replace(['%', '?'], ['%%', '%s'], $query->sql);
            $bindings = $query->connection->prepareBindings($query->bindings);
            $pdo = $query->connection->getPdo();
            $realSql = vsprintf($sqlWithPlaceholders, array_map([$pdo, 'quote'], $bindings));
            $duration = self::formatDuration($query->time / 1000);
            Log::info(sprintf('[%s] %s', $duration, $realSql));
        });
    }

    private static function formatDuration($seconds)
    {
        if ($seconds < 0.001) {
            return round($seconds * 1000000) . 'μs';
        } elseif ($seconds < 1) {
            return round($seconds * 1000, 2) . 'ms';
        }
        return round($seconds, 2) . 's';
    }
}
