<?php

namespace Intermax\LaravelOpenApi\Generator;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\PathItem;
use cebe\openapi\Writer;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use Throwable;

class Generator
{
    protected Router $router;

    protected OperationCreator $operationCreator;

    protected ComponentsCreator $componentsCreator;

    protected Repository $config;

    protected RouteAnalyser $routeAnalyser;

    protected RequestBodyCreator $requestBodyCreator;

    protected ParametersCreator $parametersCreator;

    public function __construct(
        Router $router,
        OperationCreator $operationCreator,
        ComponentsCreator $componentsCreator,
        Repository $config,
        RouteAnalyser $routeAnalyser,
        RequestBodyCreator $requestBodyCreator,
        ParametersCreator $parametersCreator
    ) {
        $this->router = $router;
        $this->operationCreator = $operationCreator;
        $this->componentsCreator = $componentsCreator;
        $this->config = $config;
        $this->routeAnalyser = $routeAnalyser;
        $this->requestBodyCreator = $requestBodyCreator;
        $this->parametersCreator = $parametersCreator;
    }

    /**
     * @return string
     * @throws Throwable
     * @throws TypeErrorException
     */
    public function generate()
    {
        $openApi = new OpenApi([
            'openapi' => '3.0.2',
            'info' => [
                'title' => $this->config->get('open-api.name', 'API'),
                'version' => $this->config->get('open-api.version', '1.0.0'),
            ],
            'paths' => [],
        ]);

        $routes = $this->router->getRoutes()->getRoutes();

        /** @var Route $route */
        foreach ($routes as $route) {
            if (
                ! $this->routeAnalyser->isApiRoute($route)
                || $this->routeAnalyser->isClosureRoute($route)
            ) {
                continue;
            }

            if (! isset($openApi->paths['/'.$route->uri])) {
                $openApi->paths['/'.$route->uri] = new PathItem([]);
            }

            foreach ($route->methods as $method) {
                if (! $this->routeAnalyser->isApiHttpMethod($method)) {
                    continue;
                }

                $operationName = strtolower($method);

                $requestClassName = $this->routeAnalyser->determineRequestClass($route);

                if ($requestClassName) {
                    $requestClass = new $requestClassName();

                    $requestBody = $this->requestBodyCreator->create($requestClass);
                }

                $resourceClassName = $this->routeAnalyser->determineResourceClass($route);

                $openApi->paths['/'.$route->uri]->$operationName = $this->operationCreator->create(
                   method: $method,
                   entity: $this->deriveEntityNameFromUri($route->uri()),
                   resource: last(explode('\\', $resourceClassName ?? Str::studly(str_replace('/', '-', $route->uri())))),
                   requestBody: $requestBody ?? null,
                   parameters: $this->parametersCreator->create($route, $requestClass ?? null)
                );

                unset($requestBody);
            }
        }

        $openApi->components = $this->componentsCreator->get();

        return Writer::writeToJson($openApi);
    }

    protected function deriveEntityNameFromUri(string $uri)
    {
        return Str::studly(Str::singular(explode('/', $uri)[0]));
    }
}
