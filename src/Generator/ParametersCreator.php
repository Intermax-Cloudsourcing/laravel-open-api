<?php

namespace Intermax\LaravelOpenApi\Generator;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;

class ParametersCreator
{
    public function __construct(
        protected PathParametersCreator $pathParameterCreator,
        protected FilterParametersCreator $filterParametersCreator,
    ) {
    }

    /**
     * @param  Route  $route
     * @param  null|FormRequest  $requestClass
     * @return array<mixed>
     */
    public function create(Route $route, ?FormRequest $requestClass = null): array
    {
        return array_merge(
            $this->pathParameterCreator->create($route),
            $this->filterParametersCreator->create($route, $requestClass),
        );
    }
}
