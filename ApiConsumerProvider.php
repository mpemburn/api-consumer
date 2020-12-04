<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Mpemburn\ApiConsumer\Handlers\ResponseHandler;
use Mpemburn\ApiConsumer\Interfaces\ResponseHandlerInterface;

class ApiConsumerProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind ResponseHandlerInterface class(es)
        app()->bind(ResponseHandlerInterface::class, ResponseHandler::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/api-consumer.php' => config_path('api-consumer.php'),
        ], 'config');
    }
}
