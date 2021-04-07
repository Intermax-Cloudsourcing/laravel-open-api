<?php

namespace Intermax\LaravelOpenApi\Contracts;

interface FilterRequest
{
    /**
     * @return array<Filter>
     */
    public function filters(): array;
}
