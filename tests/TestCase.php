<?php

namespace Tests;

use HttpClient\Facades\Http;
use HttpClient\ClientServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Setups the test suit
     */
    public function setUp(): void
    {
        parent::setUp();

        Http::fake();
    }
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [ClientServiceProvider::class];
    }
}
