<?php

namespace HttpClient\Clients;

use Exception;
use Illuminate\Support\Arr;
use HttpClient\HttpResponse;
use HttpClient\UrlSignature;
use GuzzleHttp\Psr7\Response;
use HttpClient\Contracts\HttpClient;
use Illuminate\Support\Facades\Request;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Support\Traits\Macroable;

abstract class BaseClient implements HttpClient
{
    /**
     * The url signature
     * @var \HttpClient\UrlSignature
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
     * Handles response errors
     * @param  Exception $e
     * @return mixed
     */
    abstract protected function handleResponseErrors(Exception $e);

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
     * Adds an http option
     * @param string $key
     * @param string $value
     * @param null|mixed $default
     */
    public function getOption($key, $default = null)
    {
        return Arr::get($this->options, $key, $default);
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
     * Sets the url signature
     * @param UrlSignature $signature
     */
    public function setSignature(UrlSignature $signature)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Gets the url signature
     * @return \HttpClient\UrlSignature|null
     */
    public function signature()
    {
        return $this->signature;
    }

    /**
     * Invokes a given callback handling errors
     * @param  callable $callback
     * @return mixed
     */
    protected function catchRequestErrors(callable $callback)
    {
        try {
            return call_user_func($callback);
        } catch (Exception $e) {
            $this->handleResponseErrors($e);
            throw $e;
        }
    }

    /**
     * Transforms a successful response
     * @param  ResponseInterface $response
     * @return mixed
     */
    protected function transformResponse($response)
    {
        // if the given value is an instance of an http response
        // we will go ahead and trigger the success handlers

        if ($response instanceof ResponseInterface) {
            return tap(new HttpResponse($response), function ($response) {
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
}
