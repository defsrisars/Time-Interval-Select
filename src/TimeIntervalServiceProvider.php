<?php

namespace ariby\TimeIntervalSelect;

use Illuminate\Support\ServiceProvider;

class TimeIntervalServiceProvider extends ServiceProvider
{
    protected $commands = [
        Commands\GetIDs::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands($this->commands);
        //
    }
}
