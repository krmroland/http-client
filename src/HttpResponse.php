<?php

namespace HttpClient;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Arr;

class HttpResponse
{
    /**
     * Creates an instance of this class
     * @param Data\Utils\Http|null $client
     */
    public function __construct(Response $response, $client)
    {
        $this->response = $response;

        $this->client = $client;
    }

    /**
     * Channel all dynamic method calls to the response
     * @param  string $method
     * @param  array $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->response->{$method}(...$arguments);
    }

    /**
     * Channel all dynamic getters to the response itself
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->response->{$key};
    }

    /**
     * Gets the response data
     * @return array
     */
    public function data($key = null, $default = null)
    {
        // The response data is a stream object that is stored in a temporary file
        // For large json files , we may want to write a parser that reads this file in bits
        $data = json_decode($this->response->getBody()->getContents(), true);

        return $key ? Arr::get($data, $key, $default) : $data;
    }
}
