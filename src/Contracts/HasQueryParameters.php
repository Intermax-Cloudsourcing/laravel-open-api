<?php

namespace Intermax\LaravelOpenApi\Contracts;

use Intermax\LaravelOpenApi\Generator\Parameters\QueryParameter;

interface HasQueryParameters
{
    /**
     * @return array<QueryParameter>
     */
    public function queryParameters(): array;
}
