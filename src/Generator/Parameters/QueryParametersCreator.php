<?php

namespace Intermax\LaravelOpenApi\Generator\Parameters;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\spec\Parameter;
use Illuminate\Foundation\Http\FormRequest;
use Intermax\LaravelOpenApi\Contracts\HasQueryParameters;

class QueryParametersCreator
{
    /**
     * @return array<Parameter>
     *
     * @throws TypeErrorException
     */
    public function create(?FormRequest $formRequest = null): array
    {
        if (! ($formRequest instanceof HasQueryParameters)) {
            return [];
        }

        $parameters = [];

        foreach ($formRequest->queryParameters() as $parameter) {
            $parameters[] = $parameter->toParameter();
        }

        return $parameters;
    }
}
