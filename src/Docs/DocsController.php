<?php

namespace Intermax\LaravelOpenApi\Docs;

use cebe\openapi\exceptions\TypeErrorException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Intermax\LaravelOpenApi\Generator\Generator;
use Throwable;

class DocsController
{
    /**
     * @param Generator $generator
     * @param ResponseFactory $responses
     * @return JsonResponse
     * @throws Throwable
     * @throws TypeErrorException
     */
    public function docsJson(Generator $generator, ResponseFactory $responses): JsonResponse
    {
        return $responses->json(json_decode($generator->generate()), 200, [], JSON_PRETTY_PRINT);
    }

    public function docs(Factory $views): View
    {
        return $views->make('open-api::docs');
    }
}
