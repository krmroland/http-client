<?php

namespace HttpClient\Contracts;

use HttpClient\UrlSignature;

interface HttpClient
{
    /**
     * Turn off url signing
     * @return $this
     */
    public function withoutSignature();

    /**
     * Signs a given api request
     * @param  string $apiKey
     * @param  string $apiUrl
     * @return $this
     */
    public function signUrl($apiKey, $apiUrl);
}
