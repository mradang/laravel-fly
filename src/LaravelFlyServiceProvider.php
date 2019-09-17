<?php

namespace mradang\LaravelFly;

use Illuminate\Support\ServiceProvider;

class LaravelFlyServiceProvider extends ServiceProvider {

    public function boot() {
        $this->configure();

        $this->registerSqlLog();
        $this->registerRoutes();
        $this->registerCommands();
        $this->registerMigrations();
        $this->registerGuard();
        $this->registerRouteMiddleware();
        $this->registerTrustedProxiesMiddleware();
    }

    protected function configure() {
        $this->app->configure('fly');

        $this->mergeConfigFrom(
            __DIR__.'/../config/fly.php', 'fly'
        );
    }

    protected function registerSqlLog() {
        if (config('fly.sql_log')) {
            Services\QueryLogService::log();
        }
    }

    protected function registerRoutes() {
        \Illuminate\Support\Facades\Route::group([
            'prefix' => config('fly.uri'),
            'namespace' => 'mradang\LaravelFly\Controllers',
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        });
    }

    protected function registerCommands() {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\MakeRouteDescFileCommand::class,
                Console\PublishCommand::class,
                Console\RefreshRbacNodeCommand::class,
                Console\MySQLDiffCommand::class,
            ]);
        }
    }

    protected function registerMigrations() {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/migrations');
        }
    }

    protected function registerGuard() {
        $this->app['auth']->viaRequest('api', function ($request) {
            $user = Services\UserService::checkToken($request);
            return $user ?: null;
        });
    }

    protected function registerRouteMiddleware() {
        $this->app->middleware([
            Middleware\CorsMiddleware::class,
        ]);
        $this->app->routeMiddleware([
            'auth.basic' => Middleware\Authenticate::class,
            'auth' => Middleware\Authorization::class,
        ]);
    }

    protected function registerTrustedProxiesMiddleware() {
        $this->app->middleware([
            Middleware\TrustedProxiesMiddleware::class,
        ]);
    }

}