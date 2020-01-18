<?php

namespace HttpClient\Clients;

use Exception;
use GuzzleHttp\Client;
use HttpClient\SignUrl;
use Illuminate\Support\Arr;
use GuzzleHttp\HandlerStack;
use HttpClient\HttpResponse;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Fluent;
use HttpClient\HttpClientException;
use Illuminate\Support\Facades\Request;
use GuzzleHttp\Exception\GuzzleException;

abstract class BaseClient
{
    /**
     * The url signature
     * @var \HttpClient\SignUrl|null
     */
    protected $signature;
    /**
     * The http based options
     * @var array
     */
    protected $options = [
        'stream' => true,
        'headers' => ['Content-Type' => 'Application/json'],
    ];

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
     * The error handlers
     * @var array
     */
    protected $errorHandlers = [];
    /**
     * The success handlers
     * @var array
     */
    protected $successHandlers = [];

    /**
     * The recorded history entries
     * @var array
     */
    protected $history = [];

    /**
     * Channel all dynamic calls to the client
     * @param  string $method
     * @param  array $arguments
     * @param mixed $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->invokeHandlingErrors(function () use ($method, $args) {
            // we will channel any dynamic calls to the client instance
            return $this->transformResponse($this->client()->{$method}(...$args));
        });
    }

    /**
     * Adds an http option
     * @param string $key
     * @param string $value
     */
    public function addOption($key, $value = null)
    {
        Arr::set($this->options, $key, $value);

        return $this;
    }

    /**
     * The client responsible for making requests
     * @return \GuzzleHttp\Client
     */
    public function client()
    {
        if (!$this->client) {
            $this->client = $this->makeClientInstance();
        }

        return $this->client;
    }

    /**
     * Dumps this instance
     * @return $this
     */
    public function dump()
    {
        dump($this);
        return $this;
    }

    /**
     * Gets the error handlers
     * @return array
     */
    public function getErrorHandlers()
    {
        return $this->errorHandlers;
    }

    /**
     * Gets the handler stack
     * @return \GuzzleHttp\HandlerStack
     */
    public function getHandlerStack()
    {
        if (!$this->handlerStack) {
            $this->handlerStack = HandlerStack::create($this->getHandler());
        }
        return $this->handlerStack;
    }

    /**
     * Gets the available options
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Gets the success handlers
     * @return array
     */
    public function getSuccessHandlers()
    {
        return $this->successHandlers;
    }

    /**
     * Add http error handlers
     * @param  callable $callback
     * @return $this
     */
    public function onHttpError(callable $callback)
    {
        $this->errorHandlers[] = $callback;

        return $this;
    }

    /**
     * Add http sucess handlers
     * @param  callable $callback
     * @return $this
     */
    public function onHttpSuccess(callable $callback)
    {
        $this->successHandlers[] = $callback;

        return $this;
    }

    /**
     * Makes an http request
     * @param  string $method
     * @param  array $arguments
     * @return mixed
     */
    public function request($method, ...$arguments)
    {
        return $this->invokeHandlingErrors(function () use ($method, $arguments) {
            return $this->transformResponse($this->client->request($method, ...$arguments));
        });
    }

    /**
     * Determines if a request has a valid signature
     * @param  Request $request
     * @param  string  $key
     * @param mixed $algorithm
     * @return boolean
     */
    public function requestHasValidSignature(Request $request, $key, $algorithm = 'sha256')
    {
        $query = Arr::query(Arr::except($request->query(), 'signature'));

        $original = rtrim($request->url() . '?' . $query, '?');

        $url = str_replace('http://', 'https://', $original);

        $signature = hash_hmac($algorithm, $url, $key);

        return hash_equals($signature, (string) $request->query('signature', ''));
    }

    /**
     * Sets the base uri
     * @param string $base_uri
     * @param mixed $url
     * @return  $this
     */
    public function setBaseUri($url)
    {
        return $this->addOption('base_uri', rtrim($url, '/'));
    }

    /**
     * Signs request url
     * @param  SignUrl $signed
     * @return $this
     */
    public function signUrl(SignUrl $signature)
    {
        $this->signature = $signature;

        $this->getHandlerStack()->push($signature, 'signature');

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
     * Hooks into the guzzle middleware stack
     * @param  callable $callback
     * @return $this
     */
    public function withStack(callable $callback)
    {
        call_user_func_array($callable, [$this->handlerStack]);

        return $this;
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
     * Gets the default Handler
     * @return mixed
     */
    protected function getHandler()
    {
        return null;
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
            $this->triggerHandlers($this->errorHandlers, $exception->getResponse());
        }

        throw new HttpClientException($exception);
    }

    /**
     * Invokes a given callback handling errors
     * @param  callable $callback
     * @return mixed
     */
    protected function invokeHandlingErrors(callable $callback)
    {
        try {
            return call_user_func($callback);
        } catch (Exception $e) {
            $this->handleResponseErrors($e);
            throw $e;
        }
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

    /**
     * Transforms a successful response
     * @param  Response $response
     * @return mixed
     */
    protected function transformResponse($response)
    {
        // if the given value is an instance of an http response
        // we will go ahead and trigger the success handlers

        if ($response instanceof Response) {
            return tap(new HttpResponse($response, $this->client()), function ($response) {
                $this->triggerHandlers($this->successHandlers, $response);
            });
        }

        return $response;
    }
    /**
     * Trigggers handles
     * @param  array  $handlers
     * @param  mixed $params
     * @return  void
     */
    protected function triggerHandlers(array $handlers, $params)
    {
        foreach ($handlers as $handler) {
            call_user_func_array($handler, [$params]);
        }
    }
    /**
     * Records api history
     * @return $this
     */
    public function recordHistory()
    {
        return $this;
    }
    /**
     * Gets the api history
     * @return array
     */
    public function getHistory()
    {
        return collect($this->history)->mapInto(Fluent::class);
    }

    /**
     * Clears history
     * @return $this
     */
    public function clearHistory()
    {
        $this->history = [];

        return $this;
    }
}
