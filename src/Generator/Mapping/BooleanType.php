<?php

namespace Intermax\LaravelOpenApi\Generator\Mapping;

class BooleanType extends Property
{
    public function __construct(
        ?string $description = null,
        mixed $example = null,
    ) {
        parent::__construct(
            'boolean',
            description: $description,
            example: $example,
        );
    }
}
