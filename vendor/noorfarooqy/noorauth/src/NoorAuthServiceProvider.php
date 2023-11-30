<?php

namespace Noorfarooqy\NoorAuth;

use Illuminate\Support\ServiceProvider;

class NoorAuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . "/../routes/web.php");
        $this->loadRoutesFrom(__DIR__ . "/../routes/api.php");
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'noorauth');
        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/noorauth'),
        ], 'noorauth-public');

        $this->publishes([
            __DIR__ . '/../config/error_codes.php' => config_path('error_codes.php'),
        ], 'noorauth-errors');

        $this->publishes([
            __DIR__ . '/../config/noor-auth.php' => config_path('noor-auth.php'),
        ], 'noorauth-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations/'),
        ], 'noorauth-database');
    }
    public function register()
    {
    }
}
