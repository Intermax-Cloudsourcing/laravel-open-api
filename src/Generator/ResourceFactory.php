<?php

namespace Intermax\LaravelOpenApi\Generator;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use Intermax\LaravelOpenApi\Generator\Attributes\UsesModel;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;
use ReflectionException;
use Throwable;

class ResourceFactory
{
    public function __construct(
        protected DocBlockFactory $docBlockFactory,
        protected Application $app,
        protected Repository $config,
    ) {
    }

    /**
     * @param class-string $resourceClassName
     * @return JsonResource|ResourceCollection|null
     * @throws ReflectionException|BindingResolutionException|Throwable
     */
    public function createFromClassName(string $resourceClassName): JsonResource | ResourceCollection | null
    {
        $model = $this->discoverResourceModel($resourceClassName);

        if (! $model) {
            return null;
        }

        return new $resourceClassName($model);
    }

    /**
     * @param class-string $resourceClassName
     * @return mixed
     * @throws BindingResolutionException
     * @throws ReflectionException
     * @throws Throwable
     */
    protected function discoverResourceModel(string $resourceClassName): mixed
    {
        $reflectionClass = new ReflectionClass($resourceClassName);

        if ($reflectionClass->isSubclassOf(ResourceCollection::class)) {
            $properties = $reflectionClass->getDefaultProperties();

            if ($properties['collects']) {
                return new Collection([$this->discoverResourceModel($properties['collects'])]);
            }
        }

        return new ResourceInput();
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @return mixed
     * @throws BindingResolutionException
     */
    protected function discoverFromDocBlockProperty(ReflectionClass $reflectionClass): mixed
    {
        $propertyTags = $this->docBlockFactory
            ->create($reflectionClass->getDocComment())->getTagsByName('property');

        foreach ($propertyTags as $tag) {
            assert($tag instanceof Property);

            $type = $tag->getType();

            assert($type instanceof Object_);

            $class = (string) $type->getFqsen();

            if (class_exists($class)) {
                return $this->app->make($class);
            }
        }

        return null;
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @return mixed
     * @throws BindingResolutionException
     */
    protected function discoverFromAttribute(ReflectionClass $reflectionClass): mixed
    {
        $attributes = $reflectionClass->getAttributes(UsesModel::class);

        if (empty($attributes)) {
            return null;
        }

        /** @var UsesModel $attribute */
        $attribute = $attributes[0]->newInstance();

        $className = $attribute->getClassName();

        if (class_exists($className)) {
            return $this->app->make($className);
        }

        return null;
    }
}
