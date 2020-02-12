<?php
namespace HttpClient\Clients\Fakes;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler as GuzzleMockHandler;

class FakeRequests
{
    /**
     * The base guzzle mock handler
     * @var GuzzleMockHandler
     */
    protected $handler;
    /**
     * Mocked requests
     * @var array
     */
    protected $mocks = [];
    /**
     * The executed requests
     * @var array
     */
    protected $executed = [];

    /**
     * Mocks an http request
     * @param  string  $url
     * @param  strinh  $content
     * @param  integer $statusCode
     * @param  array   $headers
     * @param mixed $verb
     * @return $response
     */
    public function add($url, $content, $verb = 'get', $statusCode = 200, $headers = [])
    {
        $response = new Response($statusCode, $headers, json_encode($content));

        Arr::set($this->mocks, FakeRequest::joinVerbAndUrl($verb, $url), $response);

        return $response;
    }

    /**
     * The executed requests
     * @return COllection
     */
    public function executed()
    {
        return new ExecutedRequests($this->executed);
    }

    /**
     * Gets the mocked requests
     * @return array
     */
    public function getValues()
    {
        return array_values($this->mocks);
    }

    /**
     * Invoke this class as a function
     * @return Callable
     */
    public function recordHistory()
    {
        return function (callable $handler) {
            return function ($request, array $options) use ($handler) {
                if (!$this->requestIsMocked($request)) {
                    return $this->rejectUnMockedRequest($request);
                }

                return $handler($request, $options)->then(
                    function ($value) use ($request, $options) {
                        return $this->handleSuccessFullResponse($request, $value, $options);
                    },
                    function ($error) use ($request, $options) {
                        return $this->handleFailedResponse($request, $error, $options);
                    }
                );
            };
        };
    }

    /**
     * Adds a given request to the executed list
     * @param Request      $request
     * @param FakeRequest $faked
     */
    protected function addRequestToExecutedList($request, FakeRequest $faked)
    {
        $path = FakeRequest::requestKeyPath($request);

        Arr::add($this->executed, $path, []);

        $this->executed[$path][] = $faked;
    }

    /**
     * Records failed responses
     * @param  Request $request
     * @param  mixed $error
     * @param  array $options
     * @return $this
     */
    protected function handleFailedResponse($request, $error, $options)
    {
        $this->addRequestToExecutedList(
            $request,
            new FakeRequest($request, null, $options, $error)
        );

        return \GuzzleHttp\Promise\rejection_for($error);
    }

    /**
     * Handles a successful response
     * @param  Request $request
     * @param  mixed $value
     * @param  array $options
     * @param mixed $response
     * @return mixed
     */
    protected function handleSuccessFullResponse($request, $response, $options)
    {
        $this->addRequestToExecutedList($request, new FakeRequest($request, $response, $options));

        return $response;
    }

    /**
     * Reject un mocked request
     * @param  Request $request
     * @return Promise
     */
    protected function rejectUnMockedRequest($request)
    {
        return \GuzzleHttp\Promise\promise_for(
            new Response(
                404,
                [],
                null,
                1.1,
                vsprintf('%s %s has not been mocked', [$request->getMethod(), $request->getUri()])
            )
        );
    }

    /**
     * Handle request class
     * @param  \GuzzleHttp\Psr7\Request $request
     * @return void
     */
    protected function requestIsMocked($request)
    {
        return Arr::has($this->mocks, FakeRequest::requestKeyPath($request));
    }
}
