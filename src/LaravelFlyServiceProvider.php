<?php

namespace mradang\LaravelFly;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Auth;

class LaravelFlyServiceProvider extends ServiceProvider
{

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // 配置文件
            $this->publishes([
                \dirname(__DIR__).'/config/fly.php' => config_path('fly.php'),
            ], 'config');
            // 快捷脚本
            $this->publishes([
                \dirname(__DIR__).'/publishes/shortcut/' => base_path(),
            ], 'shortcut');
            // 运维脚本
            $this->publishes([
                \dirname(__DIR__).'/publishes/deploy/' => base_path('deploy'),
            ], 'deploy');
        }

        $this->registerSqlLog();
        $this->registerRoutes();
        $this->registerCommands();
        $this->registerMigrations();
        $this->registerGuard();
        $this->registerRouteMiddleware();
        $this->registerTrustedProxiesMiddleware();
    }

    public function register()
    {
        $this->mergeConfigFrom(
            \dirname(__DIR__).'/config/fly.php', 'fly'
        );
    }

    protected function registerSqlLog()
    {
        if (config('fly.sql_log')) {
            Services\QueryLogService::log();
        }
    }

    protected function registerRoutes()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/routes.php');
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\MakeRouteDescFileCommand::class,
                Console\RefreshRbacNodeCommand::class,
                Console\MySQLDiffCommand::class,
            ]);
        }
    }

    protected function registerMigrations()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(\dirname(__DIR__).'/migrations/');
        }
    }

    protected function registerGuard()
    {
        Auth::viaRequest('token', function ($request) {
            $user = Services\UserService::checkToken($request);
            return $user ?: null;
        });
    }

    protected function registerRouteMiddleware()
    {
        // 跨域中间件
        $kernel = $this->app->make(Kernel::class);
        $kernel->prependMiddleware(Middleware\CorsMiddleware::class);

        // 认证中间件
        Auth::shouldUse('api'); // 默认使用 api 认证
        $this->app['router']->aliasMiddleware('auth.basic', Middleware\Authenticate::class);
        $this->app['router']->aliasMiddleware('auth', Middleware\Authorization::class);
    }

    protected function registerTrustedProxiesMiddleware()
    {
        $this->app['router']->middleware([
            Middleware\TrustedProxiesMiddleware::class,
        ]);
    }

}