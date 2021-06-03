<?php

namespace Intermax\LaravelOpenApi\Generator;

use Intermax\LaravelOpenApi\Generator\Attributes\MapsFields;
use Intermax\LaravelOpenApi\Generator\Mapping\Mapper;
use Intermax\LaravelOpenApi\Generator\Mapping\Schema;
use ReflectionClass;
use ReflectionException;

class ResourceAnalyser
{
    /**
     * @param class-string $className
     * @throws ReflectionException
     */
    public function retrieveMappingFromResource(string $className): ?Schema
    {
        $reflectionClass = new ReflectionClass($className);

        $attributes = $reflectionClass->getAttributes(MapsFields::class);

        if (empty($attributes)) {
            return null;
        }

        $mapsFields = $attributes[0]->newInstance();

        assert($mapsFields instanceof MapsFields);

        $className = $mapsFields->getClassName();

        /** @var Mapper $mapper */
        $mapper = new $className();

        return $mapper->map();
    }
}
