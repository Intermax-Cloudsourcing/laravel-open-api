<?php

namespace Intermax\LaravelOpenApi\Generator\Values;

class NumberValue extends Value
{
    public function getType(): string
    {
        return 'number';
    }

    public function cast(mixed $value): mixed
    {
        return (float) $value;
    }
}
