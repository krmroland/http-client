<?php

namespace HttpClient;

use HttpClient\Contracts\HttpClient;

class HttpClientException extends \Exception
{
    /**
     * The exception being rendered
     * @var Exception
     */
    protected $exception;
    /**
     * The client making the exception
     * @var HttpClient
     */
    protected $client;

    /**
     * Creates an instance of the exception
     * @param Exception $exception
     */
    public function __construct($exception, HttpClient $client)
    {
        parent::__construct($exception->getMessage());

        $this->exception = $exception;
        $this->client = $client;
    }

    /**
     * Renders central server exception into a laravel exception
     * @return Response
     */
    public function render()
    {
        if ($response = $this->getResponse()) {
            return $this->transformResponse($response);
        }

        return response()->json(['message' => $this->exception->getMessage()], 500);
    }

    /**
     * Transforms a guzzle response into a laravel response
     * @param  GuzzleHttp\Psr7\Response $response
     * @return Illuminate\Http\Response
     */
    protected function transformResponse($response)
    {
        return response()->json(
            array_merge((array) json_decode($response->getBody()->getContents() ?? [], true), [
                'reason' => $response->getReasonPhrase(),
            ]),
            $response->getStatusCode()
        );
    }
    /**
     * Gets the exception response
     * @return \GuzzleHttp\Psr7\Response|null
     */
    public function getResponse()
    {
        //some exceptions don't have responses
        return $this->exception->hasResponse() ? $this->exception->getResponse() : null;
    }
    /**
     * The Http client
     * @return \HttpClient\Contracts\HttpClient
     */
    public function getClient()
    {
        return $this->client;
    }
}
