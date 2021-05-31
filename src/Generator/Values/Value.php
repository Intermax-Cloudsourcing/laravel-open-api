<?php

namespace Intermax\LaravelOpenApi\Generator\Values;

use Closure;
use JsonSerializable;

abstract class Value implements JsonSerializable
{
    public function __construct(
        protected Closure $closure,
        protected bool $shouldCast = true,
    ) {
    }

    public function getValue(): mixed
    {
        return call_user_func(Closure::fromCallable($this->closure));
    }

    public function jsonSerialize()
    {
        $value = $this->getValue();

        if (! is_null($value) && $this->shouldCast) {
            return $this->cast($value);
        }

        return $value;
    }

    abstract public function getType(): string;

    protected function cast(mixed $value): mixed
    {
        return $value;
    }
}
