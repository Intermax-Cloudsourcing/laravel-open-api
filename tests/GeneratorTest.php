<?php

namespace Intermax\LaravelOpenApi\Tests;

use Illuminate\Routing\Router;
use Intermax\LaravelOpenApi\Generator\Generator;
use Intermax\LaravelOpenApi\OpenApiServiceProvider;
use Intermax\LaravelOpenApi\Tests\Utilities\ResourceInput\ThingController;
use Orchestra\Testbench\TestCase;

class GeneratorTest extends TestCase
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
     */
    public function it_generates_open_api_spec_for_route()
    {
        /** @var Generator $generator */
        $generator = app(Generator::class);
        $spec = json_decode($generator->generate(), true);

        $this->assertNotNull($spec['paths']['/things']['post'] ?? null);

        $this->assertEquals('postThingResource', $spec['paths']['/things']['post']['operationId'] ?? null);
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
}
