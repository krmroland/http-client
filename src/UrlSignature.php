<?php

namespace HttpClient;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;

class UrlSignature
{
    /**
     * The api key
     * @var string
     */
    protected $key;

    /**
     * The ai secret
     * @var string
     */
    protected $secret;
    /**
     * The algorithm used for signing requests
     * @var string
     */
    protected $algorithm;

    /**
     * Creates an instance of this  class
     * @param string $key
     * @param string $secret
     */
    public function __construct($key, $secret, $algorithm = 'sha256')
    {
        $this->key = $key;

        $this->secret = $secret;

        $this->algorithm = $algorithm;
    }

    /**
     * Creates a signature for the request url
     * @param string $url
     * @param  Request $request
     */
    public function generateUrlSignature($url)
    {
        return hash_hmac($this->algorithm, $this->normalizeUrl($url), $this->secret);
    }

    /**
     * Get(s) the key
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get(s) the secret
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Sets the API key
     * @param  string $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * The api Secret
     * @param  string $secret
     * @return $this;
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * Determines if a request has a valid signature
     * @param  string $full url
     * @return boolean
     */
    public function urlIsSigned($fullUrl)
    {
        $request = Request::create($this->normalizeUrl($fullUrl));

        $url = $request->url();

        $queryStringsExceptSignature = Arr::query(Arr::except($request->query(), 'signature'));

        $original = rtrim($url . '?' . $queryStringsExceptSignature, '?');

        $signature = hash_hmac($this->algorithm, $original, $this->secret);

        return hash_equals($signature, (string) $request->query('signature', ''));
    }

    /**
     * Normalizes url
     * @param  string $url
     * @return string
     */
    protected function normalizeUrl($url)
    {
        // we will trim the url &  replace any http(if any) with http(s) for consistency
        // only for signing purpose(s), this doesn't change the url schema though
        return trim(str_replace('http://', 'https://', (string) $url), '/');
    }
}
