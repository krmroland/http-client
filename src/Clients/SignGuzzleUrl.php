<?php

namespace HttpClient\Clients;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Request;
use HttpClient\UrlSignature;

class SignGuzzleUrl extends UrlSignature
{
    /**
     * executes the middleware returning the next middleware
     * @param  callable $handler
     * @return callable
     */
    public function __invoke($handler)
    {
        return function ($request, array $options) use (&$handler) {
            $request = $request
                ->withUri($this->signRequest($request))
                ->withHeader('X-API-KEY', $this->apiKey);

            return $handler($request, $options);
        };
    }
    /**
     * Signs request url
     * @param  Request $request
     * @return string
     */
    protected function signRequest($request)
    {
        return Uri::withQueryValue($request->getUri(), 'signature', $this->urlSignature($request));
    }

    /**
     * Creates a signature for the request url
     * @param  Request $request
     */
    protected function urlSignature($request)
    {
        // we will trim the url & also replace any http(if any) with http(s) for consistency
        // only for signing purpose(s), this doesn't change the url schema though
        $url = trim(str_replace('http://', 'https://', (string) $request->getUri()), '/');

        return hash_hmac('sha256', $url, $this->apiSecret);
    }
}
