<?php

namespace Intermax\LaravelOpenApi\Tests;

use Illuminate\Routing\Router;
use Intermax\LaravelOpenApi\Generator\Generator;
use Intermax\LaravelOpenApi\OpenApiServiceProvider;
use Intermax\LaravelOpenApi\Tests\Utilities\Mapping\ThingController;
use Intermax\LaravelOpenApi\Tests\Utilities\Mapping\ThingMapper;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ResponseMappingTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [OpenApiServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function it_can_map_resource_with_a_mapper_class()
    {
        /** @var Generator $generator */
        $generator = app(Generator::class);
        $spec = json_decode($generator->generate(), true);

        $this->assert200ResponseExists($spec);

        $this->assertJsonStringEqualsJsonString(
            expectedJson: json_encode((new ThingMapper)->map()),
            actualJson: json_encode(
                $this->getSchema($spec)
            )
        );
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

    /**
     * @param  array<mixed>  $spec
     * @return array<mixed>
     */
    protected function getSchema(array $spec): array
    {
        return $spec['paths']['/things']['post']['responses']['200']['content']['application/json']['schema'];
    }
}
