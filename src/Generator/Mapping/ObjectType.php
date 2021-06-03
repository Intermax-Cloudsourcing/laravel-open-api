<?php

namespace Intermax\LaravelOpenApi\Generator\Mapping;

class ObjectType extends Property
{
    public function __construct(
        ?string $description = null,
        mixed $example = null,
        ?array $properties = null,
    ) {
        parent::__construct(
            type: 'object',
            description: $description,
            example: $example,
            properties: $properties,
        );
    }
}
