<?php

namespace HttpClient;

use HttpClient\Clients\ClientManager;
use Illuminate\Support\ServiceProvider;

class ClientServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ClientManager::class, function ($app) {
            return new ClientManager($app);
        });
    }
}
