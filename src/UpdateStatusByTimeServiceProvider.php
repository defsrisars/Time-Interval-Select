<?php

namespace Ariby\UpdateStatusByTime;

use Illuminate\Support\ServiceProvider;
use Ariby\UpdateStatusByTime\Commands\GetSatisfyIDs;

class UpdateStatusByTimeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // 執行所有套件 migrations
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
            // 註冊所有 commands
            $this->commands([
                GetSatisfyIDs::class
            ]);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([

        ]);
    }
}
