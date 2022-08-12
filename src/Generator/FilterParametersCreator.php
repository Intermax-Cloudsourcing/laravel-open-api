<?php

namespace Intermax\LaravelOpenApi\Generator;

use cebe\openapi\spec\Parameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Intermax\LaravelOpenApi\Contracts\FilterRequest;

class FilterParametersCreator
{
    /**
     * @param  Route  $route
     * @return array<Parameter>
     */
    public function create(Route $route, ?FormRequest $formRequest = null): array
    {
        if (! $formRequest || ! ($formRequest instanceof FilterRequest)) {
            return [];
        }

        $parameters = [];

        foreach ($formRequest->filters() as $filter) {
            foreach ($filter->parameters() as $parameterName => $type) {
                $parameters[] = new Parameter([
                    'name' => $parameterName,
                    'in' => 'query',
                    'schema' => [
                        'type' => $type,
                    ],
                ]);
            }
        }

        return $parameters;
    }
}
