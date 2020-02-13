<?php

namespace HttpClient\Clients\Fakes;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use HttpClient\Clients\Fakes\FakeRequest;

class ExecutedRequests extends Collection
{
    /**
     * The faked requests
     * @var array
     */
    protected $requests;
    /**
     * Creates an instance of this class
     * @param array $requests
     */
    public function __construct(array $requests)
    {
        $this->requests = $requests;

        $data = [];

        parent::__construct(Arr::flatten($requests));
    }
    /**
     * Finds a request by url and verb
     * @param   $url  string
     * @param  string $verb
     * @return collection
     */
    public function find($url, $verb = 'GET')
    {
        $key = FakeRequest::joinVerbAndUrl($verb, $url);

        return $this->filter(function ($item) use ($key) {
            return $item->key === $key;
        })->toArray();
    }
}
