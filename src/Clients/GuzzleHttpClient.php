<?php

namespace HttpClient\Clients;

use Exception;
use GuzzleHttp\Client;
use HttpClient\SignUrl;
use GuzzleHttp\HandlerStack;
use HttpClient\HttpResponse;
use GuzzleHttp\Psr7\Response;
use HttpClient\HttpClientException;
use Illuminate\Support\Facades\Request;
use GuzzleHttp\Exception\GuzzleException;

class GuzzleHttpClient extends BaseClient
{
    /**
     * The client instance
     * @var \GuzzleHttp\Client
     */
    protected $client;
    /**
     * The handler responsible for http requests
     * @var Handlers
     */
    protected $handlerStack;

    /**
     * Channel all dynamic calls to the client
     * @param  string $method
     * @param  array $arguments
     * @param mixed $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->catchRequestErrors(function () use ($method, $args) {
            // we will channel any dynamic calls to the client instance
            return $this->transformResponse($this->client()->{$method}(...$args));
        });
    }

    /**
     * Makes an http request
     * @param  string $method
     * @param  array $arguments
     * @return mixed
     */
    public function request($method, ...$arguments)
    {
        return $this->catchRequestErrors(function () use ($method, $arguments) {
            return $this->transformResponse($this->client()->request($method, ...$arguments));
        });
    }
    /**
     * Signs a given api request
     * @param  string $apiKey
     * @param  string $apiSecret
     * @return $this
     */
    public function signUrl($apiKey, $apiSecret)
    {
        $this->signature = new SignGuzzleUrl($apiKey, $apiSecret);

        $this->getHandlerStack()->push($this->signature, 'signature');

        return $this;
    }

    /**
     * The request signature
     * @return SignedUrl
     */
    public function signature()
    {
        return $this->signature;
    }

    /**
     * Turn off url signing
     * @return $this
     */
    public function withoutSignature()
    {
        $this->handlerStack->remove('signature');

        return $this;
    }

    /**
     * The client responsible for making requests
     * @return \GuzzleHttp\Client
     */
    protected function client()
    {
        if (!$this->client) {
            $this->client = $this->makeClientInstance();
        }

        return $this->client;
    }

    /**
     * Gets the default Handler
     * @return mixed
     */
    protected function getHandler()
    {
        return null;
    }

    /**
     * Gets the handler stack
     * @return \GuzzleHttp\HandlerStack
     */
    protected function getHandlerStack()
    {
        if (!$this->handlerStack) {
            $this->handlerStack = HandlerStack::create($this->getHandler());
        }
        return $this->handlerStack;
    }

    /**
     * Handles response errors
     * @param  Exception $exception
     * @return \HttpClient\HttpClientException
     */
    protected function handleResponseErrors(Exception $exception)
    {
        if (!$exception instanceof GuzzleException) {
            throw $exception;
        }

        if ($exception->hasResponse()) {
            $this->triggerHandlers(
                $this->errorHandlers,
                new HttpResponse($exception->getResponse())
            );
        }

        throw new HttpClientException($exception, $this);
    }

    /**
     * Creates a client instance
     * @return Client
     */
    protected function makeClientInstance()
    {
        $this->options['handler'] = $this->getHandlerStack();

        return new Client($this->options);
    }
}
