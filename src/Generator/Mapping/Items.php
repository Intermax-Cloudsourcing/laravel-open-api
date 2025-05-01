<?php

namespace Intermax\LaravelOpenApi\Generator\Mapping;

use JsonSerializable;

class Items implements JsonSerializable
{
    public function __construct(
        protected string $type,
        protected ?string $format = null,
    ) {}

    public function jsonSerialize(): mixed
    {
        $json = [
            'type' => $this->type,
        ];

        if ($this->format) {
            $json['format'] = $this->format;
        }

        return $json;
    }
}
