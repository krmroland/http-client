<?php

namespace HttpClient\Clients\Fakes;

use Illuminate\Support\Str;

class FakeRequest
{
    /**
     * The request object
     * @var Request
     */
    public $request;
    /**
     * The error variable
     * @var Error
     */
    public $error;
    /**
     * The Response
     * @var Response
     */
    public $response;

    /**
     * The request options
     * @var array
     */
    public $options;

    /**
     * Mocked request key path
     * @param  string $verb
     * @param  strong $url
     * @return string
     */
    public static function joinVerbAndUrl($verb, $url)
    {
        return sprintf('%s-%s', Str::lower($verb), Str::slug(trim($url, '/')));
    }

    /**
     * Calculates the request key path
     * @param  \GuzzleHttp\Psr7\Request $request
     * @return string
     */
    public static function requestKeyPath($request)
    {
        return static::joinVerbAndUrl($request->getMethod(), $request->getUri()->getPath());
    }

    /**
     * Creates an instance of this class
     * @param Request $request
     * @param Response $response
     * @param Error $error
     * @param array $options
     */
    public function __construct($request, $response, $options, $error = null)
    {
        $this->request = $request;
        $this->response = $response;
        $this->options = $options;
        $this->error = $error;
    }
}
