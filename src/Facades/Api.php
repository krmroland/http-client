<?php

namespace HttpClient\Facades;

use HttpClient\Clients\ClientManager;
use Illuminate\Support\Facades\Facade;

class Api extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @throws \RuntimeException
     * @return string
     *
     */
    protected static function getFacadeAccessor()
    {
        return ClientManager::class;
    }
}
