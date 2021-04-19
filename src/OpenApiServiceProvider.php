<?php

namespace Intermax\LaravelOpenApi;

use Illuminate\Support\ServiceProvider;
use phpDocumentor\Reflection\DocBlockFactory;

class OpenApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'open-api');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->publishes([
            __DIR__.'/../config/open-api.php' => config_path('open-api.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/open-api.php', 'open-api');

        $this->app->bind(DocBlockFactory::class, function () {
            return DocBlockFactory::createInstance();
        });
    }
}
