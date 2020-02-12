<?php

namespace HttpClient;

use HttpClient\Contracts\HttpClient;
use Illuminate\Support\ServiceProvider;
use HttpClient\Clients\GuzzleHttpClient;

class ClientServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(HttpClient::class, GuzzleHttpClient::class);
    }
}
