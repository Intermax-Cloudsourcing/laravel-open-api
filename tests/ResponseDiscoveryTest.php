<?php

namespace Intermax\LaravelOpenApi\Tests;

use cebe\openapi\exceptions\TypeErrorException;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Intermax\LaravelOpenApi\Generator\Generator;
use Intermax\LaravelOpenApi\OpenApiServiceProvider;
use Intermax\LaravelOpenApi\Tests\Utilities\ResourceInput\ThingController;
use Orchestra\Testbench\TestCase;

class ResponseDiscoveryTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [OpenApiServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws TypeErrorException
     */
    public function it_can_map_a_resource_with_openapi_types_through_response_discovery()
    {
        /** @var Generator $generator */
        $generator = app(Generator::class);
        $spec = json_decode($generator->generate(), true);

        $this->assert200ResponseExists($spec);

        $this->assertNumberOfSubThingsIsInteger($spec);

        $this->assertFractionalNumberIsNumber($spec);
    }

    /**
     * @param  Router  $router
     * @return void
     */
    protected function defineRoutes($router)
    {
        $router->middleware(['api'])->group(function ($router) {
            $router->apiResource('things', ThingController::class)->only('store');
        });
    }

    /**
     * @param  array<mixed>  $spec
     */
    protected function assert200ResponseExists(array $spec): void
    {
        $this->assertNotNull($spec['paths']['/things']['post']['responses']['200'] ?? null);
    }

    protected function assertNumberOfSubThingsIsInteger($spec): void
    {
        $this->assertEquals(
            'integer',
            Arr::get(
                $spec,
                'components.schemas.Thing.properties.attributes.properties.numberOfSubThings.type'
            )
        );
    }

    protected function assertFractionalNumberIsNumber($spec): void
    {
        $this->assertEquals(
            'number',
            Arr::get(
                $spec,
                'components.schemas.Thing.properties.attributes.properties.fractionalNumber.type'
            )
        );
    }
}
