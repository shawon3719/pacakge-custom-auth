<?php

namespace sws\smartauth\Providers;

use Carbon\Laravel\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {

        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'smartauth');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->publishes([
            __DIR__.'/../public/css' => public_path('vendor/sws-auth/css'),
        ], 'sws-auth');
        $this->publishes([
            __DIR__.'/../public/js' => public_path('vendor/sws-auth/js'),
        ], 'sws-auth');

        $this->publishes([
            __DIR__.'/../config/sws-auth.php' => config_path('sws-auth.php'),
        ], 'sws-auth');

    }

    public function register()
    {

    }

}