<?php

namespace HttpClient;

class HttpClientException extends \Exception
{
    /**
     * The exception being rendered
     * @var Exception
     */
    protected $exception;

    /**
     * Creates an instance of the exception
     * @param Exception $exception
     */
    public function __construct($exception)
    {
        parent::__construct($exception->getMessage());

        $this->exception = $exception;
    }

    /**
     * Renders central server exception into a laravel exception
     * @return Response
     */
    public function render()
    {
        if ($this->exception->hasResponse()) {
            return $this->transformResponse($this->exception->getResponse());
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
            array_merge(json_decode($response->getBody()->getContents() ?? [], true), [
                'reason' => $response->getReasonPhrase(),
            ]),
            $response->getStatusCode()
        );
    }
}
