<?php

namespace Intermax\LaravelOpenApi\Generator;

class ResourceInput implements \JsonSerializable
{
    public function __isset(string $name): bool
    {
        // This prevents resources accidentally thinking a relation is loaded by only returning true for the id property.
        if ($name == 'id') {
            return true;
        }

        return false;
    }

    public function __get(string $name): string
    {
        return 'value';
    }

    /**
     * @param  array<mixed>  $arguments
     */
    public function __call(string $name, array $arguments): string
    {
        return 'value';
    }

    public function __toString(): string
    {
        return 'value';
    }

    public function jsonSerialize(): mixed
    {
        return (string) $this;
    }
}
