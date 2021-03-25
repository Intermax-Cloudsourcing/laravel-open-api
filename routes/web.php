<?php

use Illuminate\Support\Facades\Route;
use Intermax\LaravelOpenApi\Docs\DocsController;

Route::get('/docs/json', DocsController::class.'@docsJson');
Route::get('/docs', DocsController::class.'@docs');
