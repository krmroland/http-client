<?php

namespace HttpClient;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Illuminate\Support\Arr;
use RuntimeException;

class HttpClient
{
    /**
     * The base url
     * @var string|null
     */
    public static $baseUrl;

    /**
     * The response class
     * @var string
     */
    public static $responseClass = HttpResponse::class;

    /**
     * The request signature
     * @var App\CentralServer\Http\SignedUrl
     */
    protected $signature;

    /**
     * The middle ware stack
     * @var \GuzzleHttp\HandlerStack
     */
    protected $stack;

    /**
     * Creates an instance of the Http class
     */
    public function __construct($apiKey = null, $apiSecret = null)
    {
        $this->stack = HandlerStack::create();

        $this->signature = new SignUrl($apiKey, $apiSecret);

        if ($apiKey && $apiSecret) {
            $this->stack->push($this->signature, 'signature');
        }

        $this->options = $this->getDefaultOptions();
    }

    /**
     * Handles any dynamic calls to this class
     * @param  string $method
     * @param  array $arguments
     * @return Response
     */
    public function __call($method, $arguments)
    {
        if (! in_array($method, ['get', 'put', 'post', 'options', 'head', 'delete', 'patch', 'request'], true)) {
            throw new RuntimeException('Call to Undefined method ' . $method);
        }
        return $this->makeRequest($method, $arguments);
    }

    /**
     * Sets the global base url
     * @param  string $url
     * @return $this
     */
    public static function setGlobalBaseUri($url)
    {
        static::$baseUrl = rtrim($url, '/');
    }

    /**
     * Sets the instance base url
     * @param  string $value
     * @return string
     */
    public function baseUri($value)
    {
        return Arr::set($this->options, 'base_uri', rtrim($value, '/'));
    }

    /**
     * Adds an option to the available options if it doesn't exist
     * @param string $key
     * @param string $value
     */
    public function addOption($key, $value)
    {
        Arr::set($this->options, $key, $value);

        return $this;
    }

    /**
     * Caches the response for the specified time
     * @param  int $time time in minutes
     * @param  string $tag
     * @return $this
     */
    public function cache($time, $tag = 'http')
    {
        $this->options['handler']->push(new HttpResponseCache($time, $tag));

        return $this;
    }

    /**
     * Gets the default client options
     * @return array
     */
    public function getDefaultOptions()
    {
        return [
            'base_uri' => static::$baseUrl,
            'handler' => $this->stack,
            'stream' => true,
            'headers' => ['Content-Type' => 'Application/json'],
        ];
    }

    /**
     * Sets a response class
     * @return $this
     */
    public function responseClass(string $responseClass)
    {
        $this->responseClass = $responseClass;

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
        call_user_func_array($callable, [$this->stack]);

        return $this;
    }

    /**
     * Turn off url signing
     * @return $this
     */
    public function withoutSignature()
    {
        $this->stack->remove('signature');

        return $this;
    }

    /**
     * Executes a request
     * @param  string $method
     * @param  array $arguments
     * @return Data\Utils\HttpResponse
     */
    protected function makeRequest($method, $arguments)
    {
        try {
            $response = (new Client($this->options))->{$method}(...$arguments);
            return new static::$responseClass($response, $this);
        } catch (\Exception $e) {
            throw new HttpClientException($e);
        }
    }
}
