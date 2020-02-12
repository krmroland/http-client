<?php

namespace Tests;

use HttpClient\CLients\ApiClient;

class DummyApi extends ApiClient
{
    protected $defaultOptions = ['base_uri' => 'https://dummy.example.com'];

    /**
     * Gets the default options
     * @return array
     */
    public function getDefaultOptions()
    {
        return $this->defaultOptions;
    }
}
