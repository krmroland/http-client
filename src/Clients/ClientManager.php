<?php

namespace HttpClient\Clients;

use Illuminate\Support\Manager;

class ClientManager extends Manager
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return env('HTTP_CLIENT_DRIVER', 'http');
    }
    /**
     * Creates an instance of the http client
     * @return HttpClient
     */
    public function createHttpDriver()
    {
        return new HttpClient();
    }
    /**
     * Creates a test driver
     * @return \HttpClient\Clients\MockClient
     */
    public function createTestDriver()
    {
        return new MockClient();
    }
}
