<?php

namespace Intermax\LaravelOpenApi\Tests\Utilities\Mapping;

use Illuminate\Routing\Controller;
use Intermax\LaravelOpenApi\Generator\ResourceInput;

class ThingController extends Controller
{
    public function store(): ThingResource
    {
        return new ThingResource(new ResourceInput);
    }
}
