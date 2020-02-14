<?php

namespace Tests;

use HttpClient\Facades\Http;
use HttpClient\HttpClientException;

class HttpClientTest extends TestCase
{
    public function test_it_sets_the_base_uri()
    {
        $api = Http::setBaseUri('https://example.com');

        $this->assertContains(['base_uri' => 'https://example.com'], $api->getOptions());
    }

    public function test_http_get_option()
    {
        $api = Http::setBaseUri('https://example.com');

        $this->assertEquals($api->getOption('base_uri'), 'https://example.com');
    }

    public function test_it_throws_an_exception_if_agiven_request_is_not_mocked()
    {
        $this->expectException(HttpClientException::class);

        try {
            Http::mockRequest('api/v1/test', ['message' => 'Hello world'])->get(
                'api/v1/unmocked-request'
            );
        } catch (HttpClientException $e) {
            $this->assertEquals($e->getResponse()->getStatusCode(), 404);
            throw $e;
        }
    }

    public function test_it_sets_the_client_on_http_client_exception()
    {
        $this->expectException(HttpClientException::class);

        try {
            Http::mockRequest('api/v1/test', ['message' => 'Hello world'])->get(
                'api/v1/unmocked-request'
            );
        } catch (HttpClientException $e) {
            $this->assertEquals(Http::getFacadeRoot(), $e->getClient());
            throw $e;
        }
    }

    public function test_it_triggers_success_handlers_after_a_call()
    {
        Http::onHttpSuccess(function ($response) {
            $this->assertEquals($response->data(), ['message' => 'Hello world']);
        })
            ->mockRequest('api/v1/test', ['message' => 'Hello world'])
            ->get('/api/v1/test');
    }

    public function test_it_triggers_error_response_handlers_after_a_call()
    {
        $this->expectException(HttpClientException::class);

        Http::setBaseUri('https://example.com')
            ->onHttpError(function ($response) {
                $this->assertEquals($response->data(), ['error' => 'something went wrong']);
            })
            ->mockRequest('/test', ['error' => 'something went wrong'], 'get', 422)
            ->get('/test');
    }

    public function test_it_signs_a_url()
    {
        $api = Http::signUrl('key', 'secret')
            ->setBaseUri('https://example.com')
            ->mockRequest('test', []);

        $api->get('test');

        // we need to make sure the apu was sent with the signature
        $entry = with($api->requests()->executed())->first();

        $this->assertTrue($api->signature()->urlIsSigned($entry->request->getUri()));
    }

    public function test_it_url_signature_passes_for_http_routes()
    {
        $api = Http::signUrl('key', 'secret')
            ->setBaseUri('https://example.com')
            ->mockRequest('test', []);

        $api->get('test');

        // we need to make sure the apu was sent with the signature
        $entry = with($api->requests()->executed())->first();

        $this->assertTrue(
            $api->signature()->urlIsSigned($entry->request->getUri()->withScheme('http'))
        );
    }

    public function test_assert_executed_passes_if_a_request_is_made()
    {
        Http::onHttpSuccess(function ($response) {
            $this->assertEquals($response->data(), ['message' => 'Hello world']);
        })
            ->mockRequest('api/v1/tests', ['message' => 'Hello world'])
            ->get('api/v1/tests');

        Http::requests()->assertExecuted('api/v1/tests');
    }
}
