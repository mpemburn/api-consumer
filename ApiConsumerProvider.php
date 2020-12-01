<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ApiConsumerProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/api-consumer.php' => config_path('api-consumer.php'),
        ], 'config');
    }
}
