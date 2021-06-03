<?php

namespace Intermax\LaravelOpenApi\Generator\Mapping;

class ArrayType extends Property
{
    public function __construct(
        ?string $description,
        mixed $example = null,
        Items $items,
    ) {
        parent::__construct(
            type: 'array',
            description: $description,
            example: $example,
            items: $items,
        );
    }
}
