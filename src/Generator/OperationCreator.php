<?php

namespace Intermax\LaravelOpenApi\Generator;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Responses;

class OperationCreator
{
    /**
     * @param  null|array<Parameter>  $parameters
     * @param  null|Responses<Response>  $responses
     *
     * @throws TypeErrorException
     */
    public function create(
        string $method,
        string $entity,
        string $resource,
        Responses $responses,
        ?RequestBody $requestBody = null,
        ?array $parameters = null,
    ): Operation {
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

        $operation['responses'] = $responses;

        return new Operation($operation);
    }
}
