<?php

declare(strict_types=1);

namespace Intermax\LaravelOpenApi\Contracts;

interface HasQueryParameters
{
    /**
     * @return array<QueryParameter>
     */
    public function queryParameters(): array;
}
