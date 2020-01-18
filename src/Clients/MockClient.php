<?php

namespace HttpClient\Clients;

use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Fluent;
use GuzzleHttp\Handler\MockHandler;

class MockClient extends BaseClient
{
    /**
     * The Http handler
     * @var \GuzzleHttp\Handler\MockHandler|null
     */
    protected $handler;

    /**
     * The mocked tests
     * @var array
     */
    protected $mocks = [];

    /**
     * The history middleward
     * @var nll
     */
    protected $historyMiddleware;

    /**
     * Creates the mock handler
     * @return MockHanlder
     */
    public function getHandler()
    {
        if (!$this->handler) {
            $this->handler = new MockHandler($this->mocks);
        }
        return $this->handler;
    }

    /**
     * Mocks a request
     * @param mixed $content
     * @param mixed $status
     * @param mixed $headers
     * @return $this
     */
    public function mock($content, $status = 200, $headers = [])
    {
        $this->getHandler()->append(new Response($status, $headers, $content));

        return $this;
    }

    /**
     * Records api history
     * @return $this
     */
    public function recordHistory()
    {
        $this->getHandlerStack()->push($this->getHistoryMiddleware());

        return $this;
    }

    /**
     * Gets the history middleware
     * @return  \Closure
     */
    protected function getHistoryMiddleware()
    {
        if (!$this->historyMiddleware) {
            $this->historyMiddleware = Middleware::history($this->history);
        }

        return $this->historyMiddleware;
    }
}
