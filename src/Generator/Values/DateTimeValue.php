<?php

namespace Intermax\LaravelOpenApi\Generator\Values;

use Closure;

class DateTimeValue extends Value
{
    public function __construct(Closure $closure)
    {
        parent::__construct($closure, shouldCast: false);
    }

    public function getType(): string
    {
        return 'date-time';
    }
}
