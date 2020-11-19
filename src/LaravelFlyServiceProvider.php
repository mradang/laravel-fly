<?php

namespace mradang\LaravelFly;

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
        }

        $this->registerSqlLog();
        $this->registerCommands();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/fly.php', 'fly');
    }

    protected function registerSqlLog()
    {
        if (config('fly.sql_log')) {
            Services\QueryLogService::log();
        }
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\MySQLDiffCommand::class,
            ]);
        }
    }
}
