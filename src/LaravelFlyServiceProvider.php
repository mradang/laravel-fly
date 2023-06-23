<?php

namespace mradang\LaravelFly;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class LaravelFlyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // 配置文件
            $this->publishes([
                \dirname(__DIR__) . '/config/fly.php' => config_path('fly.php'),
            ], 'config');
            // 快捷脚本
            $this->publishes([
                \dirname(__DIR__) . '/publishes/shortcut/' => base_path(),
            ], 'shortcut');
            // 运维脚本
            $this->publishes([
                \dirname(__DIR__) . '/publishes/deploy/' => base_path('deploy'),
            ], 'deploy');
            // docker
            $this->publishes([
                \dirname(__DIR__) . '/publishes/docker/' => base_path('docker'),
            ], 'docker');

        }

        $this->registerCommands();
        $this->registerSqlLog();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/fly.php', 'fly');
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\MySQLDiffCommand::class,
                Console\MySQLStructCommand::class,
            ]);
        }
    }

    protected function registerSqlLog()
    {
        if (!config('fly.sql_log')) {
            return;
        }

        $this->app['events']->listen(QueryExecuted::class, function (QueryExecuted $query) {
            $sqlWithPlaceholders = str_replace(['%', '?', '%s%s'], ['%%', '%s', '?'], $query->sql);

            $bindings = $query->connection->prepareBindings($query->bindings);
            $pdo = $query->connection->getPdo();
            $realSql = $sqlWithPlaceholders;
            $duration = $this->formatDuration($query->time / 1000);

            if (count($bindings) > 0) {
                $realSql = vsprintf($sqlWithPlaceholders, array_map([$pdo, 'quote'], $bindings));
            }

            Log::channel(config('fly.sql_log_channel') ?: config('logging.default'))
                ->debug(sprintf(
                    '[%s: %s] [%s] %s',
                    request()->method(),
                    request()->getRequestUri(),
                    $duration,
                    $realSql,
                ));
        });
    }

    /**
     * Format duration.
     *
     * @param  float  $seconds
     * @return string
     */
    private function formatDuration($seconds)
    {
        if ($seconds < 0.001) {
            return round($seconds * 1000000) . 'μs';
        } elseif ($seconds < 1) {
            return round($seconds * 1000, 2) . 'ms';
        }

        return round($seconds, 2) . 's';
    }
}
