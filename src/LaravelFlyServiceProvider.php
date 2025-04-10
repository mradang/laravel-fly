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

            //  registerCommands
            $this->commands([
                Console\MySQLDiffCommand::class,
                Console\MySQLStructCommand::class,
            ]);
        }
    }
}
