<?php

namespace Intermax\LaravelOpenApi\Docs;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class DocsController
{
    public function docsJson(ResponseFactory $responses): JsonResponse
    {
        return $responses->json();
    }

    public function docs(Factory $views): View
    {
        return $views->make('open-api::docs');
    }
}
