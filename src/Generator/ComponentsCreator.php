<?php

namespace App\OpenApi;

use Carbon\Carbon;
use cebe\openapi\spec\Components;
use Faker\Generator as Faker;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ComponentsCreator
{
    protected array $schemas = [];

    protected Faker $faker;

    protected DatabaseManager $db;

    public function __construct(Faker $faker, DatabaseManager $db)
    {
        $this->faker = $faker;
        $this->db = $db;
    }

    /**
     * @param string $entityName
     * @return $this
     * @throws \Throwable
     */
    public function addEntity(string $entityName)
    {
        if ($this->schemas[$entityName] ?? false) {
            return $this;
        }

        $this->db->beginTransaction();

        /** @var Model $entity */
        if (is_a("App\\{$entityName}", Model::class, true)) {
            $entity = factory("App\\{$entityName}")->create();
        } else {
            $entity = factory("App\\{$entityName}")->make();
        }

        $this->loadAllRelations($entity);

        $this->db->rollBack();

        $fillableFields = $entity->getFillable();

        $dates = $entity->getDates();

        $resourceName = "\\App\\Http\\Resources\\{$entityName}";

        /** @var JsonResource $resource */
        $resource = new $resourceName($entity);

        $properties = [];

        $resourceArray = json_decode(json_encode($resource));

        foreach ($resourceArray as $item => $value) {
            $property = [];

            if (!in_array($item, $fillableFields)) {
                $property['readOnly'] = true;
            }

            $properties[$item] = array_merge($property, $this->determineProperty($dates, $item, $value));
        }

        $this->schemas[$entityName] = [
            'type' => 'object',
            'description' => '',
            'properties' => $properties,
            'example' => $resourceArray
        ];

        return $this;
    }

    /**
     * @return Components
     * @throws \cebe\openapi\exceptions\TypeErrorException
     */
    public function get()
    {
        return new Components(['schemas' => $this->schemas]);
    }

    protected function determineProperty($dates, $item, $value): array
    {
        $property = [
            'type' => gettype($value)
        ];

        if (in_array($item, $dates) || $value instanceof Carbon) {
            $property['type'] = 'string';
            $property['format'] = 'date-time';
        }

        if ($property['type'] == 'array') {

            $firstItem = $value[array_key_first($value)] ?? null;

            $property['items'] = [
                'type' => gettype($firstItem)
            ];

            if ($property['items']['type'] == 'object') {
                $subProperties = [];

                foreach ($firstItem as $subField => $subValue) {
                    $subProperties[] = [$subField => $this->determineProperty($dates, $subField, $subValue)];
                }

                $property['items']['properties'] = $subProperties;
            }
        }

        return $property;
    }

    /**
     * @param $entity
     * @throws \ReflectionException
     */
    protected function loadAllRelations($entity)
    {
        $reflectionClass = new \ReflectionClass($entity);

        foreach($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->hasReturnType() && Str::contains($method->getReturnType()->getName(), [
                'HasMany',
                'BelongsTo',
                'BelongsToMany'
            ])) {
                $entity->load([$method->getName()]);
            }
        }
    }
}
