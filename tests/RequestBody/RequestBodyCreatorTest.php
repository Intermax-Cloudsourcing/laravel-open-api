<?php

namespace Intermax\LaravelOpenApi\Tests;

use Intermax\LaravelOpenApi\Generator\RequestBodyCreator;
use Intermax\LaravelOpenApi\Tests\QueryParameters\Utilities\ThingCollectionRequest;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RequestBodyCreatorTest extends TestCase
{
    #[Test]
    public function it_omits_query_parameters_from_request_body()
    {
        /** @var RequestBodyCreator $creator */
        $creator = $this->app->make(RequestBodyCreator::class);

        $output = $creator->create($this->app->make(ThingCollectionRequest::class));

        $this->assertNull($output);
    }

    #[Test]
    public function it_does_not_fail_with_exception_when_rules_method_requires_additional_arguments()
    {
        /** @var RequestBodyCreator $creator */
        $creator = $this->app->make(RequestBodyCreator::class);

        $output = $creator->create($this->app->make(ThingCollectionRequest::class));

        $this->assertNull($output);
    }
}
