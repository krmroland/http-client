<?php

namespace HttpClient\Clients\Fakes;

use Illuminate\Support\Arr;
use GuzzleHttp\Handler\MockHandler;
use HttpClient\Clients\GuzzleHttpClient;
use PHPUnit\Framework\Assert as PhpUnit;

class FakeGuzzleHttpClient extends GuzzleHttpClient
{
    /**
     * The mock handler
     * @var \HttpClient\Clients\FakeRequests
     */
    protected $fakeRequests;
    /**
     * The request handler
     * @var \GuzzleHttp\Handler\MockHandler
     */
    protected $mockHandler;
    /**
     * Assert that a reqyesr has been made
     * @param mixed $verb
     * @param mixed $url
     * @return void
     */
    public function assertRequestExceuted($verb, $url)
    {
        $respnse = Arr::get($this->executedRequests, $this->requestKeyPath($verb, $url));

        PhpUnit::assertNotNull($respnse);
    }

    /**
     * Creates the mock handler
     * @return MockHanlder
     */
    public function getHandler()
    {
        if (!$this->mockHandler) {
            $requests = $this->requests();

            $this->mockHandler = new MockHandler($requests->getValues());
        }

        return $this->mockHandler;
    }
    /**
     * The request mock
     * @return \HttpClient\Clients\MockHandler
     */
    public function requests()
    {
        if (!$this->fakeRequests) {
            $this->fakeRequests = new FakeRequests();
        }

        return $this->fakeRequests;
    }

    /**
     * Mocks an http request
     * @param $args
     * @return $this
     */
    public function mockRequest(...$args)
    {
        $this->getHandler()->append($this->requests()->add(...$args));

        return $this;
    }

    /**
     * Creates a client instance
     * @return Client
     */
    protected function makeClientInstance()
    {
        $this->getHandlerStack()->push($this->requests()->recordHistory());

        return parent::makeClientInstance();
    }
}
