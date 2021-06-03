<?php

namespace Intermax\LaravelOpenApi\Generator\Mapping;

class StringType extends Property
{
    public function __construct(
        ?string $description = null,
        ?string $format = null,
        mixed $example = null,
    ) {
        parent::__construct(
            type: 'string',
            description: $description,
            format: $format,
            example: $example,
        );
    }
}
