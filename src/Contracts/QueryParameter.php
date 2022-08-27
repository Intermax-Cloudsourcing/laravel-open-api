<?php

declare(strict_types=1);

namespace Intermax\LaravelOpenApi\Contracts;

use cebe\openapi\spec\Parameter;

interface QueryParameter
{
    public function toParameter(): Parameter;
}
