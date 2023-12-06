<?php

namespace Noorfarooqy\Flexcube;

use Illuminate\Support\ServiceProvider;

class FlexcubeServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/flexcube.php' => config_path('flexcube.php'),
        ], 'flexcube-config');

    }

    public function register()
    {
    }
}
