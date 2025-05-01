<?php

namespace Intermax\LaravelOpenApi\Generator;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Server;
use cebe\openapi\Writer;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use Intermax\LaravelOpenApi\Generator\Parameters\ParametersCreator;

class Generator
{
    public function __construct(
        protected Router $router,
        protected OperationCreator $operationCreator,
        protected ComponentManager $componentManager,
        protected Repository $config,
        protected RouteAnalyser $routeAnalyser,
        protected RequestBodyCreator $requestBodyCreator,
        protected ParametersCreator $parametersCreator,
        protected ResponsesCreator $responsesCreator,

    ) {}

    /**
     * @param  string  $output  json or yaml
     *
     * @throws TypeErrorException
     */
    public function generate(string $output = 'json'): string
    {
        $openApi = new OpenApi([
            'openapi' => '3.0.2',
            'info' => [
                'title' => $this->config->get('open-api.name') ?? $this->config->get('app.name'),
                'version' => $this->config->get('open-api.version', '1.0.0'),
            ],
            'servers' => [
                new Server([
                    'url' => $this->config->get('app.url'),
                ]),
            ],
            'paths' => [],
        ]);

        $routes = $this->router->getRoutes()->getRoutes();

        foreach ($routes as $route) {
            if (
                ! $this->routeAnalyser->isApiRoute($route)
                || $this->routeAnalyser->isClosureRoute($route)
            ) {
                continue;
            }

            $path = '/'.ltrim($route->uri, '/');

            if (! isset($openApi->paths[$path])) {
                $openApi->paths[$path] = new PathItem([]);
            }

            foreach ($route->methods as $method) {
                if (! $this->routeAnalyser->isApiHttpMethod($method)) {
                    continue;
                }

                $operationName = strtolower($method);

                $operation = $this->buildOperation($route, $method);

                $openApi->paths[$path]->$operationName = $operation;
            }
        }

        $openApi->components = $this->componentManager->components();

        return match ($output) {
            'yaml' => Writer::writeToYaml($openApi),
            default => Writer::writeToJson($openApi),
        };
    }

    protected function deriveEntityNameFromUri(string $uri): string
    {
        $parts = array_reverse(explode('/', $uri));

        foreach ($parts as $part) {
            if (! str_contains($part, '{')) {
                return Str::of($part)
                    ->studly()
                    ->singular()
                    ->toString();
            }
        }

        return Str::of(last($parts))
            ->studly()
            ->singular()
            ->toString();
    }

    protected function getOperationId(string $method, string $uri): string
    {
        $operationId = Str::of($method)->lower();

        foreach (explode('/', $uri) as $part) {
            if (str_contains($part, '{')) {
                $operationIdParts = $operationId->explode('-');

                $operationId = Str::of($operationIdParts->push(
                    Str::singular(
                        (string) $operationIdParts->pop()
                    )
                )->implode('-'));

                $nextIterationsAreNested = true;

                continue;
            }

            if ($nextIterationsAreNested ?? false) {
                $operationIdPart = $part;
            } else {
                $operationIdPart = Str::plural($part);
            }

            $operationId = $operationId->append('-'.$operationIdPart);
        }

        return $operationId->camel()->toString();
    }

    public function buildOperation(Route $route, string $method): Operation
    {
        $entityName = $this->deriveEntityNameFromUri($route->uri());

        $requestClassName = $this->routeAnalyser->determineRequestClass($route);

        if ($requestClassName) {
            /** @var FormRequest $requestClass */
            $requestClass = new $requestClassName;

            $requestBody = $this->requestBodyCreator->create($requestClass, $entityName);
        }

        $resourceClassName = $this->routeAnalyser->determineResourceClass($route);

        if ($resourceClassName) {
            $response = $this->responsesCreator->createFromResource($resourceClassName, $entityName);
        }

        return $this->operationCreator->create(
            method: $method,
            entity: $entityName,
            operationId: $this->getOperationId($method, $route->uri()),
            responses: $response ?? $this->responsesCreator->emptyResponse(),
            requestBody: $requestBody ?? null,
            parameters: $this->parametersCreator->create($route, $requestClass ?? null),
        );
    }
}
