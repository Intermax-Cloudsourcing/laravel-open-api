<?php

declare(strict_types=1);

namespace Intermax\LaravelOpenApi\Contracts;

use cebe\openapi\spec\Parameter;

interface QueryParameter
{
    public function name(): string;

    public function type(): string;

    public function toParameter(): Parameter;
}
