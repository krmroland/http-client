<?php

namespace Tests;

use HttpClient\CLients\ApiClient;
use HttpClient\Clients\Fakes\FakeGuzzleHttpClient;

class ApiClientTest extends TestCase
{
    public function test_api_clients_resolves_the_default_http_client()
    {
        $api = app(ApiClient::class);

        $this->assertInstanceOf(FakeGuzzleHttpClient::class, $api->getClient());
    }

    public function test_it_sets_the_default_options_on_the_parent_client()
    {
        $api = new DummyApi();

        $this->assertContains($api->getDefaultOptions(), $api->getOptions());
    }
}
