<?php

namespace Intermax\LaravelOpenApi\Generator;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\spec\Parameter;
use Illuminate\Routing\Route;

class PathParametersCreator
{
    /**
     * @param Route $route
     * @return array<Parameter>
     * @throws TypeErrorException
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
                'required' => true,
                'schema' => [
                    'type' => 'string',
                ],
            ]);
        }

        return $parameters;
    }
}
