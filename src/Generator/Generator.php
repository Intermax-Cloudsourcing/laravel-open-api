<?php

namespace App\OpenApi;

use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\PathItem;
use cebe\openapi\Writer;
use Exception;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;

class Generator
{
    protected Router $router;

    protected OperationCreator $operationCreator;

    protected ComponentsCreator $componentsCreator;

    public function __construct(Router $router, OperationCreator $operationCreator, ComponentsCreator $componentsCreator)
    {
        $this->router = $router;
        $this->operationCreator = $operationCreator;
        $this->componentsCreator = $componentsCreator;
    }

    public function generate()
    {
        $openApi = new OpenApi([
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'Time API',
                'version' => '0.0.1'
            ],
            'paths' => []
        ]);

        $routes = $this->router->getRoutes()->getRoutes();


        foreach ($routes as $route) {
            if (!$this->isApiRoute($route)) {
                continue;
            }

            if (!isset($openApi->paths['/' . $route->uri])) {
                $openApi->paths['/' . $route->uri] = new PathItem([]);
            }

            foreach ($route->methods as $method) {
                if (!$this->isApiMethod($method)) {
                    continue;
                }

                $operationName = strtolower($method);

                $action = $this->determineAction($method, $route->uri);

                $entityName = $this->determineEntityName($method, $route->uri);

                $this->componentsCreator->addEntity(ucfirst($entityName));

                $openApi->paths['/' . $route->uri]->$operationName = $this->operationCreator->create($action, $route->uri, $entityName);
            }
        }

        $openApi->components = $this->componentsCreator->get();

        return Writer::writeToJson($openApi);
    }

    protected function isApiRoute(Route $route): bool
    {
        foreach ($route->gatherMiddleware() as $middleware) {
            if ($middleware == 'api') {
                return true;
            }
        }

        return false;
    }

    protected function isApiMethod(string $method): bool
    {
        return in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']);
    }

    protected function determineAction(string $method, string $uri): string
    {
        switch (strtolower($method)) {
            case 'get':
                if (Str::endsWith($uri, '}')) {
                    return 'show';
                }

                return 'index';
            case 'post':
                return 'store';
            case 'put':
            case 'patch':
                return 'update';
            case 'delete':
                return 'destroy';
        }

        throw new Exception('Operation method not recognized.');
    }

    protected function determineEntityName(string $method, string $uri): string
    {
        $chunks = explode('/', $uri);

        $lastChunk = array_pop($chunks);

        if (in_array($this->determineAction($method, $uri), ['index', 'store'])) {
            $entity = $lastChunk;
        } else {
            $entity = array_pop($chunks);
        }

        return Str::singular(Str::studly($entity));
    }
}
