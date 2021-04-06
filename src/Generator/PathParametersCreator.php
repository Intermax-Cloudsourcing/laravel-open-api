<?php

namespace Intermax\LaravelOpenApi\Generator;

use cebe\openapi\spec\Parameter;
use Illuminate\Routing\Route;

class PathParametersCreator
{
    /**
     * @param Route $route
     * @return array<Parameter>
     */
    public function create(Route $route): array
    {
        if (! $route->parameterNames()) {
            return [];
        }

        $parameters = [];

        foreach ($route->parameterNames() as $parameterName) {
            $parameters[] = new Parameter([
                'name' => $parameterName,
                'in' => 'path',
                'schema' => [
                    'type' => 'string',
                ],
            ]);
        }

        return $parameters;
    }
}
