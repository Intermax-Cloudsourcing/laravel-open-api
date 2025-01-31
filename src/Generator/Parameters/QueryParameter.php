<?php

declare(strict_types=1);

namespace Intermax\LaravelOpenApi\Generator\Parameters;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\spec\Parameter;

class QueryParameter implements \Intermax\LaravelOpenApi\Contracts\QueryParameter
{
    /**
     * @param  array<int, mixed>|null  $options
     */
    public function __construct(
        public readonly string $name,
        public readonly string $type = 'string',
        public readonly ?array $options = null,
        public readonly mixed $example = null,
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function type(): string
    {
        return $this->type;
    }

    /**
     * @throws TypeErrorException
     */
    public function toParameter(): Parameter
    {
        $data = [
            'name' => $this->name,
            'in' => 'query',
            'schema' => [
                'type' => $this->type,
            ],
        ];

        if (! is_null($this->options)) {
            $data['schema']['enum'] = $this->options;
        }

        if (! is_null($this->example)) {
            $data['schema']['example'] = $this->example;
        }

        return new Parameter($data);
    }
}
