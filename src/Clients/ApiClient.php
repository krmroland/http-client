<?php

namespace HttpClient\CLients;

use HttpClient\Contracts\HttpClient;

class ApiClient
{
    /**
     * The HttpCLient responsible for making requests
     * @var \HttpClient\Contracts\HttpClient
     */
    protected $client;

    /**
     * Pipes all dynamic calls to the client instance
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public function __call($method, ...$args)
    {
        return $this->getClient()->{$method}(...$args);
    }

    /**
     * Creates an instance of the client class
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        foreach ($option as $key => $value) {
            $this->getClient()->addOption($key, $value);
        }
    }

    /**
     * Sets the API client
     * @param HttpClient $client
     */
    public function setClient(HttpClient $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Gets the client instance
     * @return \HttpClient\Contracts\HttpClient
     */
    protected function getClient()
    {
        if (!$this->client) {
            return $this->client;
        }
        return $this->client;
    }
}
