<?php

namespace HttpClient;

use Illuminate\Support\Arr;
use GuzzleHttp\Psr7\Response;

class HttpResponse
{
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
     * Creates an instance of this class
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
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
     * The base response
     * @return \GuzzleHttp\Psr7\Response
     */
    public function base()
    {
        return $this->response;
    }

    /**
     * Gets the response data
     * @param null|mixed $key
     * @param null|mixed $default
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
