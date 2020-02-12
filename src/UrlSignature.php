<?php

namespace HttpClient;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;

class UrlSignature
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
     * Determines if a request has a valid signature
     * @param  Request $request
     * @param  string  $key
     * @param mixed $algorithm
     * @return boolean
     */
    public static function requestIsSigned(Request $request, $key, $algorithm = 'sha256')
    {
        $query = Arr::query(Arr::except($request->query(), 'signature'));

        $original = rtrim($request->url() . '?' . $query, '?');

        $url = str_replace('http://', 'https://', $original);

        $signature = hash_hmac($algorithm, $url, $key);

        return hash_equals($signature, (string) $request->query('signature', ''));
    }
}
