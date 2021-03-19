<?php

namespace Intermax\LaravelOpenApi\Generator;

use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Responses;

class OperationCreator
{
    /**
     * @param string $method
     * @param string $resource
     * @param null|RequestBody $requestBody
     * @param null|array<Parameter> $parameters
     * @param null|Responses $responses
     * @return Operation
     */
    public function create(
        string $method,
        string $entity,
        string $resource,
        ?RequestBody $requestBody = null,
        ?array $parameters = null,
        ?Responses $responses = null
    ) {
        $method = strtolower($method);

        $operation = [
            'tags' => [
                $entity,
            ],
            'operationId' => "{$method}{$resource}",
        ];

        if ($requestBody) {
            $operation['requestBody'] = $requestBody;
        }

        if ($parameters) {
            $operation['parameters'] = $parameters;
        }

        $operation['responses'] = $responses ?? new Responses([]);

        return new Operation($operation);
    }
}
