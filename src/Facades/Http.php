<?php

namespace HttpClient\Facades;

use HttpClient\Contracts\HttpClient;
use Illuminate\Support\Facades\Facade;
use HttpClient\Clients\Fakes\FakeGuzzleHttpClient;

class Http extends Facade
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
        return HttpClient::class;
    }
    /**
     * Replace the bound instance with a fake.
     *
     * @return \HttpClient\Clients\FakeGuzzleHttpClient
     */
    public static function fake()
    {
        static::swap($fake = new FakeGuzzleHttpClient());

        return $fake;
    }
}
