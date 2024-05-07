<?php

namespace Intermax\LaravelOpenApi\Tests;

use Illuminate\Support\Arr;
use Intermax\LaravelOpenApi\Generator\Parameters\QueryParametersCreator;
use Intermax\LaravelOpenApi\Tests\QueryParameters\Utilities\ThingCollectionRequest;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

class QueryParameterCreatorTest extends TestCase
{
    #[Test]
    public function it_creates_query_parameters_based_on_a_form_request_class()
    {
        /** @var QueryParametersCreator $creator */
        $creator = $this->app->make(QueryParametersCreator::class);

        $output = $creator->create($this->app->make(ThingCollectionRequest::class));

        $names = Arr::pluck($output, 'name');

        $this->assertTrue(in_array('filter[test]', $names));
        $this->assertTrue(in_array('include', $names));
    }

    #[Test]
    public function it_adds_an_enum_to_query_parameters_with_options()
    {
        /** @var QueryParametersCreator $creator */
        $creator = $this->app->make(QueryParametersCreator::class);

        $output = $creator->create($this->app->make(ThingCollectionRequest::class));

        $enumArray = $output[1]->schema->enum;

        $this->assertTrue(in_array('friends', $enumArray));
        $this->assertTrue(in_array('neighbours', $enumArray));
    }
}
