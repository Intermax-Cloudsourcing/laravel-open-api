<?php

namespace Intermax\LaravelOpenApi\Generator\Mapping;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class Property implements Arrayable, JsonSerializable
{
    public function __construct(
        protected string $type,
        protected ?string $description = null,
        protected ?string $format = null,
        protected mixed $example = null,
        protected ?array $properties = null,
        protected ?Items $items = null,
    ) {}

    public function __toString(): string
    {
        return json_encode($this);
    }

    public function jsonSerialize(): mixed
    {
        return array_filter([
            'type' => $this->type,
            'description' => $this->description,
            'format' => $this->format,
            'example' => $this->example,
            'properties' => $this->properties,
            'items' => $this->items,
        ]);
    }

    public function toArray()
    {
        return json_decode(json_encode($this), true);
    }
}
