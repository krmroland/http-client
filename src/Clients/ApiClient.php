<?php

namespace HttpClient\CLients;

use HttpClient\Contracts\HttpClient;
use Illuminate\Support\Traits\ForwardsCalls;

class ApiClient
{
    use ForwardsCalls;
    /**
     * The HttpCLient responsible for making requests
     * @var \HttpClient\Contracts\HttpClient
     */
    protected $client;
    /**
     * The default client options
     * @var array
     */
    protected $defaultOptions = [];

    /**
     * Pipes all dynamic calls to the client instance
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->forwardCallTo($this->getClient(), $method, $args);
    }

    /**
     * Creates an instance of the client class
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        foreach (array_merge($this->defaultOptions, $options) as $key => $value) {
            $this->getClient()->addOption($key, $value);
        }

        $this->setUp();
    }

    /**
     * Gets the client instance
     * @return \HttpClient\Contracts\HttpClient
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->client = app(HttpClient::class);
        }
        return $this->client;
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
     * Adds a setup method
     */
    protected function setUp(): void
    {
    }
}
