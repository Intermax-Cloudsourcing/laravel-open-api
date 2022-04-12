<?php

namespace Intermax\LaravelOpenApi\Generator;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Routing\Route;
use Intermax\LaravelApi\JsonApi\Requests\FilterRequest;
use Intermax\LaravelOpenApi\Generator\Exceptions\RouteNotSupportedException;
use ReflectionClass;
use ReflectionNamedType;

class RouteAnalyser
{
    public function isApiRoute(Route $route): bool
    {
        foreach ($route->gatherMiddleware() as $middleware) {
            if ($middleware == 'api') {
                return true;
            }
        }

        return false;
    }

    public function isApiHttpMethod(string $method): bool
    {
        return in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']);
    }

    public function isClosureRoute(Route $route): bool
    {
        return $route->action['uses'] instanceof Closure;
    }

    public function determineRequestClass(Route $route)
    {
        $reflectionMethod = $this->getReflectionMethod($route);

        foreach ($reflectionMethod->getParameters() as $parameter) {
            if (! $parameter->hasType()) {
                continue;
            }

            $parameterClassName = $parameter->getType()?->getName();

            if (is_null($parameterClassName) || ! class_exists($parameterClassName)) {
                continue;
            }

            $parameterReflectionClass = new ReflectionClass($parameterClassName);

            foreach ([FilterRequest::class, FormRequest::class] as $parentClass) {
                if ($parameterReflectionClass->isSubclassOf($parentClass)) {
                    return $parameterReflectionClass->getName();
                }
            }
        }

        return null;
    }

    public function determineResourceClass(Route $route)
    {
        $reflectionMethod = $this->getReflectionMethod($route);

        $returnType = $reflectionMethod->getReturnType();

        // Check if getReturnType() returns a ReflectionNamedType (other type does not provide getName() method)
        if (! $returnType || ! $returnType instanceof ReflectionNamedType) {
            return null;
        }

        $returnTypeReflectionClass = new ReflectionClass($returnType->getName());

        if (
            $returnTypeReflectionClass->isSubclassOf(ResourceCollection::class)
            || $returnTypeReflectionClass->isSubclassOf(JsonResource::class)
        ) {
            return $returnTypeReflectionClass->getName();
        }

        return null;
    }

    protected function getReflectionMethod(Route $route)
    {
        if ($this->isClosureRoute($route)) {
            throw new RouteNotSupportedException('Cannot analyse Closure route');
        }

        $reflectionClass = new ReflectionClass($route->getController());

        $actionMethod = $route->getActionMethod();

        if ($actionMethod == get_class($route->getController())) {
            $actionMethod = '__invoke';
        }

        return $reflectionClass->getMethod($actionMethod);
    }
}
