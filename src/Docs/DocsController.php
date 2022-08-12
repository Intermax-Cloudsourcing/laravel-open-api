<?php

namespace Intermax\LaravelOpenApi\Docs;

use cebe\openapi\exceptions\TypeErrorException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Intermax\LaravelOpenApi\Generator\Generator;

class DocsController
{
    /**
     * @param  Generator  $generator
     * @param  ResponseFactory  $responses
     * @return Response
     *
     * @throws TypeErrorException
     */
    public function docsJson(Generator $generator, ResponseFactory $responses): Response
    {
        return $responses->make($generator->generate('json'))
            ->header('Content-Type', 'application/json');
    }

    /**
     * @param  Generator  $generator
     * @param  ResponseFactory  $responses
     * @return Response
     *
     * @throws TypeErrorException
     */
    public function docsYaml(Generator $generator, ResponseFactory $responses): Response
    {
        return $responses->make($generator->generate('yaml'))
            ->header('Content-Type', 'application/x-yaml');
    }

    public function docs(Factory $views): View
    {
        return $views->make('open-api::docs');
    }
}
