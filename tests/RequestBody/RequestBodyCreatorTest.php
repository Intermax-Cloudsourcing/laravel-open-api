<?php

namespace Intermax\LaravelOpenApi\Tests;

use Intermax\LaravelOpenApi\Generator\RequestBodyCreator;
use Intermax\LaravelOpenApi\Tests\QueryParameters\Utilities\ThingCollectionRequest;
use Orchestra\Testbench\TestCase;

class RequestBodyCreatorTest extends TestCase
{
    /**
     * @test
     */
    public function it_omits_query_parameters_from_request_body()
    {
        /** @var RequestBodyCreator $creator */
        $creator = $this->app->make(RequestBodyCreator::class);

        $output = $creator->create($this->app->make(ThingCollectionRequest::class));

        $this->assertNull($output);
    }
}
