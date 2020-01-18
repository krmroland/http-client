<?php

namespace Tests;

use HttpClient\SignUrl;
use HttpClient\Facades\Api;
use Illuminate\Support\Arr;
use HttpClient\HttpClientException;

class ApiClientTest extends TestCase
{
    public function test_it_sets_the_base_uri()
    {
        $api = Api::setBaseUri('https://example.com');

        $this->assertContains(['base_uri' => 'https://example.com'], $api->getOptions());
    }

    public function test_it_triggers_success_handlers_after_a_call()
    {
        $x = null;
        // Create double
        $callback = function ($response) use (&$x) {
            $x = $response->getBody()->getContents();
        };

        $api = Api::setBaseUri('https://example.com')
            ->onHttpSuccess($callback)
            ->mock('hello world')
            ->get('/test');

        $this->assertEquals($x, 'hello world');
    }

    public function test_it_triggers_error_response_handlers_after_a_call()
    {
        $error = null;

        $this->expectException(HttpClientException::class);
        // Create double
        $callback = function ($response) use (&$x) {
            $error = $response->getBody()->getContents();
        };

        $api = Api::setBaseUri('https://example.com')
            ->onHttpError($callback)
            ->mock('Something went wrong', 422)
            ->get('/test');

        $this->assertEquals($x, 'Something went wrong');
    }

    public function test_it_signs_a_url()
    {
        $signature = new SignUrl('key', 'secret');

        $api = Api::signUrl($signature)
            ->setBaseUri('https://example.com')
            ->recordHistory()
            ->mock('hello world');

        $response = $api->get('test-url');

        $item = $api->getHistory()->first();

        $signature = str_replace('signature=', '', $item->request->getUri()->getQuery());

        $this->assertTrue(
            hash_equals($signature, hash_hmac('sha256', 'https://example.com/test-url', 'secret'))
        );
    }
}
