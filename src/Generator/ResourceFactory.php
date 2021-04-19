<?php

namespace Intermax\LaravelOpenApi\Generator;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Intermax\LaravelOpenApi\Generator\Attributes\UsesModel;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;
use ReflectionException;

class ResourceFactory
{
    public function __construct(
        protected DocBlockFactory $docBlockFactory,
        protected Application $app,
        protected DatabaseManager $db,
    ) {
    }

    /**
     * @param class-string $resourceClassName
     * @return JsonResource|ResourceCollection|null
     * @throws ReflectionException|BindingResolutionException
     */
    public function createFromClassName(string $resourceClassName): JsonResource | ResourceCollection | null
    {
        if (str_contains($resourceClassName, 'Collection')) {
            return null;
        }

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
     */
    protected function discoverResourceModel(string $resourceClassName): mixed
    {
        $reflectionClass = new ReflectionClass($resourceClassName);

        $model = $this->discoverFromAttribute($reflectionClass);

        if ($model) {
            return $this->attemptToFillModel($model);
        }

        $model = $this->discoverFromDocBlockProperty($reflectionClass);

        if ($model) {
            return $this->attemptToFillModel($model);
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

    protected function attemptToFillModel(mixed $model): mixed
    {
        $className = get_class($model);

        if (! is_callable([$className, 'factory'])) {
            return $model;
        }

        $this->db->beginTransaction();

        try {
            return call_user_func([$className, 'factory'])->create();
        } catch (Exception $e) {
            return $model;
        } finally {
            $this->db->rollBack();
        }
    }
}
