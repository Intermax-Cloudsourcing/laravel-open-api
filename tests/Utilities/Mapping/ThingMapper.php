<?php

namespace Intermax\LaravelOpenApi\Tests\Utilities\Mapping;

use Intermax\LaravelOpenApi\Generator\Mapping\DateTimeType;
use Intermax\LaravelOpenApi\Generator\Mapping\IntegerType;
use Intermax\LaravelOpenApi\Generator\Mapping\Mapper;
use Intermax\LaravelOpenApi\Generator\Mapping\ObjectType;
use Intermax\LaravelOpenApi\Generator\Mapping\Schema;
use Intermax\LaravelOpenApi\Generator\Mapping\StringType;

class ThingMapper implements Mapper
{
    public function map(): Schema
    {
        return new Schema(properties: [
            'data' => new ObjectType(
                properties: [
                    'id' => new IntegerType('id of the Thing.'),
                    'name' => new StringType('The name of the Thing'),
                    'createdAt' => new DateTimeType('The creation date.'),
                ]
            ),
        ]);
    }
}
