<?php

namespace Intermax\LaravelOpenApi\Generator\Attributes;

#[\Attribute]
abstract class Attribute
{
    /**
     * @param string|class-string $className
     */
    public function __construct(
        protected string $className
    ) {
    }

    /**
     * @return string|class-string
     */
    public function getClassName(): string
    {
        return $this->className;
    }
}
