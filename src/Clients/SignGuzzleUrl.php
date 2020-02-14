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
                ->withHeader('X-API-KEY', $this->getKey());

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
        return Uri::withQueryValue(
            $request->getUri(),
            'signature',
            $this->generateUrlSignature($request->getUri())
        );
    }
}
