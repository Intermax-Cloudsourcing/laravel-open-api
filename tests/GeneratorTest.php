<?php

namespace Intermax\LaravelOpenApi\Tests;

use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Intermax\LaravelOpenApi\Generator\Generator;
use Intermax\LaravelOpenApi\OpenApiServiceProvider;
use Intermax\LaravelOpenApi\Tests\Utilities\ResourceInput\PetController;
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

        $this->assertEquals('postThings', $spec['paths']['/things']['post']['operationId'] ?? null);
    }

    /**
     * @test
     */
    public function it_generates_distinct_operation_ids_with_nested_routes()
    {
        /** @var Generator $generator */
        $generator = app(Generator::class);
        $spec = json_decode($generator->generate(), true);

        $this->assertEquals('postPets', Arr::get($spec, 'paths./pets.post.operationId'));
        $this->assertEquals('postPetOwner', Arr::get($spec, 'paths./pets/{pet}/owner.post.operationId'));
    }

    /** @test */
    public function it_generates_distinct_get_operation_ids()
    {
        /** @var Generator $generator */
        $generator = app(Generator::class);
        $spec = json_decode($generator->generate(), true);

        $this->assertEquals('getPet', Arr::get($spec, 'paths./pets/{pet}.get.operationId'));
        $this->assertEquals('getPets', Arr::get($spec, 'paths./pets.get.operationId'));
    }

    /**
     * @param  Router  $router
     * @return void
     */
    protected function defineRoutes($router)
    {
        $router->middleware(['api'])->group(function ($router) {
            $router->apiResource('things', ThingController::class)->only('store');

            $router->post('pets', [PetController::class, 'store']);

            $router->post('pets/{pet}/owner', [PetController::class, 'storeOwner']);

            $router->get('pets/{pet}', [PetController::class, 'show']);
            $router->get('pets', [PetController::class, 'index']);
        });
    }
}
