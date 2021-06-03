<?php

namespace Intermax\LaravelOpenApi\Generator\Mapping;

class DateTimeType extends Property
{
    public function __construct(
        ?string $description = null,
        mixed $example = null,
    ) {
        parent::__construct(
            type: 'string',
            format: 'date-time',
            description: $description,
            example: $example,
        );
    }
}
