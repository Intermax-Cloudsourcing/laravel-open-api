<?php

namespace Intermax\LaravelOpenApi\Generator\Parameters;

use cebe\openapi\exceptions\TypeErrorException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;

class ParametersCreator
{
    public function __construct(
        protected PathParametersCreator $pathParameterCreator,
        protected QueryParametersCreator $queryParametersCreator,
    ) {
    }

    /**
     * @return array<mixed>
     *
     * @throws TypeErrorException
     */
    public function create(Route $route, ?FormRequest $requestClass = null): array
    {
        return array_merge(
            $this->pathParameterCreator->create($route),
            $this->queryParametersCreator->create($requestClass),
        );
    }
}
