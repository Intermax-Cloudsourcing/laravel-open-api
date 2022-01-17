<?php

namespace Intermax\LaravelOpenApi\Generator;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Server;
use cebe\openapi\Writer;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;

class Generator
{
    public function __construct(
        protected Router $router,
        protected OperationCreator $operationCreator,
        protected ComponentsCreator $componentsCreator,
        protected Repository $config,
        protected RouteAnalyser $routeAnalyser,
        protected RequestBodyCreator $requestBodyCreator,
        protected ParametersCreator $parametersCreator,
        protected ResponsesCreator $responsesCreator
    ) {
    }

    /**
     * @param string $output json or yaml
     * @return string
     * @throws TypeErrorException
     */
    public function generate($output = 'json')
    {
        $openApi = new OpenApi([
            'openapi' => '3.0.2',
            'info' => [
                'title' => $this->config->get('open-api.name', 'API'),
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

                if ($resourceClassName) {
                    $response = $this->responsesCreator->createFromResource($resourceClassName);
                }

                $openApi->paths['/'.$route->uri]->$operationName = $this->operationCreator->create(
                    method: $method,
                    entity: $this->deriveEntityNameFromUri($route->uri()),
                    resource: last(explode('\\', $resourceClassName ?? Str::studly(str_replace('/', '-', $route->uri())))),
                    requestBody: $requestBody ?? null,
                    parameters: $this->parametersCreator->create($route, $requestClass ?? null),
                    responses: $response ?? $this->responsesCreator->emptyResponse(),
                );

                unset($requestBody);
                unset($response);
            }
        }

        return match ($output) {
            'yaml' => Writer::writeToYaml($openApi),
            default => Writer::writeToJson($openApi),
        };
    }

    protected function deriveEntityNameFromUri(string $uri): string
    {
        return Str::studly(Str::singular(explode('/', $uri)[0]));
    }
}
