<?php

declare(strict_types=1);

namespace Intermax\LaravelOpenApi\Tests\Utilities\ResourceInput;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;

class PetController extends Controller
{
    public function index(): JsonResponse
    {
        return Response::json('success');
    }

    public function show(): JsonResponse
    {
        return Response::json('success');
    }

    public function store(): JsonResponse
    {
        return Response::json('success');
    }

    public function storeOwner(): JsonResponse
    {
        return Response::json('success');
    }
}
