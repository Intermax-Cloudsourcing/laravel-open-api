<?php

namespace Intermax\LaravelOpenApi\Tests\Utilities;

use Illuminate\Routing\Controller;

class ThingController extends Controller
{
    public function store(): ThingResource
    {
        return new ThingResource();
    }
}
