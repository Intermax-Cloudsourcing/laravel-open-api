<?php

namespace Intermax\LaravelOpenApi\Generator\Values;

class StringValue extends Value
{
    public function getType(): string
    {
        return 'string';
    }

    public function cast(mixed $value): mixed
    {
        return (string) $value;
    }
}
