<?php

declare(strict_types=1);

namespace Intermax\LaravelOpenApi\Generator;

use cebe\openapi\spec\Components;
use cebe\openapi\spec\Schema;

class ComponentManager
{
    /**
     * @var array<string, mixed>
     */
    protected array $components = [
        'schemas' => [],
    ];

    public function __construct()
    {
    }

    public function addSchema(string $name, Schema $schema): void
    {
        $schema = (array) $schema->getSerializableData();

        if (isset($this->components['schemas'][$name])) {
            $schema = array_replace_recursive($this->components['schemas'][$name], $schema);
        }

        $this->components['schemas'][$name] = $schema;
    }

    public function components(): Components
    {
        return new Components(array_filter($this->components));
    }
}
