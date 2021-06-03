<?php

namespace Intermax\LaravelOpenApi\Generator\Mapping;

class IntegerType extends Property
{
    public function __construct(
        ?string $description = null,
        mixed $example = null,
    ) {
        parent::__construct(
            type: 'integer',
            description: $description,
            example: $example,
        );
    }
}
