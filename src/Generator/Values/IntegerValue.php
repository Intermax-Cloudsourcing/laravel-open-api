<?php

namespace Intermax\LaravelOpenApi\Generator\Values;

class IntegerValue extends Value
{
    public function getType(): string
    {
        return 'integer';
    }

    public function cast(mixed $value): mixed
    {
        return (int) $value;
    }
}
