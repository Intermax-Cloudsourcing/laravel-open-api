<?php

namespace Intermax\LaravelOpenApi\Generator\Mapping;

class NumberType extends Property
{
    public function __construct(
        ?string $description = null,
        mixed $example = null,
    ) {
        parent::__construct(
            type: 'number',
            description: $description,
            example: $example,
        );
    }
}
