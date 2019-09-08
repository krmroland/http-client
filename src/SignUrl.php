<?php

namespace HttpClient;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

class SignUrl
{
    /**
     * The api key
     * @var string
     */
    protected $apiKey;

    /**
     * The ai secret
     * @var string
     */
    protected $apiSecret;

    /**
     * Creates an instance of this  class
     * @param string $apiKey
     * @param string $apiSecret
     */
    public function __construct($apiKey, $apiSecret)
    {
        $this->apiKey = $apiKey;

        $this->apiSecret = $apiSecret;
    }

    /**
     * executes the middleware returning the next middleware
     * @param  callable $handler
     * @return callable
     */
    public function __invoke($handler)
    {
        return function ($request, array $options) use (&$handler) {
            $request = $request->withUri($this->signRequestUrl($request))->withHeader('X-API-KEY', $this->apiKey);
            return $handler($request, $options);
        };
    }

    /**
     * Sets the API key
     * @param  string $key
     * @return $this
     */
    public function apiKey($key)
    {
        $this->apiKey = $key;
        return $this;
    }

    /**
     * The api Secret
     * @param  string $secret
     * @return $this;
     */
    public function apiSecret($secret)
    {
        $this->apiSecret = $secret;

        return $this;
    }

    /**
     * Signs request url
     * @param  Request $request
     * @return string
     */
    protected function signRequestUrl($request)
    {
        return Uri::withQueryValue($request->getUri(), 'signature', $this->urlSignature($request));
    }

    /**
     * Creates a signature for the request url
     * @param  Request $request
     */
    protected function urlSignature($request)
    {
        return hash_hmac('sha256', (string) $request->getUri(), $this->apiSecret);
    }
}
