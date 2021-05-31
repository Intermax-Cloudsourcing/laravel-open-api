<?php

namespace Intermax\LaravelOpenApi\Generator\Values;

class BooleanValue extends Value
{
    public function getType(): string
    {
        return 'boolean';
    }

    public function cast(mixed $value): mixed
    {
        return (bool) $value;
    }
}
