<?php

namespace Intermax\LaravelOpenApi\Generator\Mapping;

final class Schema extends Property
{
    public function __construct(
        string $type = 'object',
        ?string $description = null,
        ?string $format = null,
        ?array $properties = null,
        ?Items $items = null,
    ) {
        parent::__construct(
            type: $type,
            description: $description,
            format: $format,
            properties: $properties,
            items: $items,
        );
    }
}
