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

        parent::__construct(Arr::flatten($requests));
    }
}
