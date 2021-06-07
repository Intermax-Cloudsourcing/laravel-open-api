<?php

namespace Intermax\LaravelOpenApi\Generator;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Responses;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Intermax\LaravelOpenApi\Generator\Values\Value;

class ResponsesCreator
{
    public function __construct(
        protected Request $request,
        protected ResourceFactory $resourceFactory,
        protected ResourceAnalyser $resourceAnalyser,
    ) {
    }

    /**
     * @param string $className
     * @return Responses<Response>
     * @throws TypeErrorException
     */
    public function createFromResource(string $className): Responses
    {
        $mapping = $this->resourceAnalyser->retrieveMappingFromResource($className);

        if ($mapping) {
            return $this->convertSchemaToResponse($mapping, $className);
        }

        return $this->discoverResponse($className);
    }

    /**
     * @param mixed $schema
     * @param string|null $resourceName
     * @return Responses<Response>
     * @throws TypeErrorException
     */
    protected function convertSchemaToResponse(mixed $schema, ?string $className = null): Responses
    {
        if ($schema instanceof Arrayable) {
            $schema = $schema->toArray();
        }

        return new Responses([
            '200' => [
                'description' => (string) Arr::last(explode('\\', (string) $className)).' response.',
                'content' => [
                    // Json encode/decode because
                    'application/vnd.api+json' => ['schema' => $schema],
                ],
            ],
        ]);
    }

    /**
     * @param array<mixed>|object $responseData
     * @return array<mixed>
     */
    protected function createProperties(object | array $responseData): array
    {
        $properties = [];

        foreach ($responseData as $name => $value) {
            $type = $this->determineType($value);

            $property = [
                $name => [
                    'type' => $type,
                ],
            ];

            if ($type === 'array') {
                $property[$name]['items'] = [
                    'type' => $this->determineArrayItemType($value),
                ];
            }

            if ($type === 'date' || $type === 'date-time') {
                $property[$name]['type'] = 'string';
                $property[$name]['format'] = $type;
            }

            if ($type === 'object') {
                if (! empty($value)) {
                    $property[$name]['properties'] = $this->createProperties($value);
                }
            }

            $properties = array_merge($properties, $property);
        }

        return $properties;
    }

    protected function determineType(mixed $value): string
    {
        if ($value instanceof Value) {
            return $value->getType();
        }

        if (is_object($value)) {
            $value = json_decode((string) json_encode($value), true);

            // If it's empty it will return [] and the below if statement would return 'array', so we intercept.
            if (empty($value)) {
                return 'object';
            }
        }

        if (is_array($value)) {
            if (Arr::isAssoc($value)) {
                return 'object';
            } else {
                return 'array';
            }
        }

        try {
            $dateTime = Carbon::parse((string) $value);

            if ($dateTime->toDateString() === (string) $value) {
                return 'date';
            }

            if (
                $dateTime->toDateTimeString() === (string) $value
                || $dateTime->toISOString() === (string) $value
            ) {
                return 'date-time';
            }
        } catch (InvalidFormatException $e) {
        }

        return match (gettype($value)) {
            'integer' => 'integer',
            'boolean' => 'boolean',
            'double' => 'number',
            default => 'string',
        };
    }

    /**
     * @param array<mixed> $value
     * @return string
     */
    protected function determineArrayItemType(array $value): string
    {
        if (empty($value)) {
            return 'object';
        }

        $item = Arr::first($value);

        return $this->determineType($item);
    }

    /**
     * @return Responses<Response>
     * @throws TypeErrorException
     */
    public function emptyResponse(): Responses
    {
        return new Responses([
            '200' => [
                'description' => 'OK Response.',
                'content' => [
                    'application/vnd.api+json' => [
                        'schema' => [
                            'type' => 'object',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param class-string $className
     * @return Responses<Response>
     * @throws TypeErrorException
     */
    public function discoverResponse(string $className): Responses
    {
        try {
            $resource = $this->resourceFactory->createFromClassName($className);

            if (! $resource) {
                return $this->emptyResponse();
            }

            $responseData = $resource->toArray($this->request);
        } catch (\Throwable $e) {
            return $this->emptyResponse();
        }

        if ($resource instanceof ResourceCollection) {
            $schemaProperties = [
                'data' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => $this->createProperties($responseData[0]),
                    ],
                ],
            ];
        } else {
            $schemaProperties = [
                'data' => [
                    'type' => 'object',
                    'properties' => $this->createProperties($responseData),
                ],
            ];
        }

        return $this->convertSchemaToResponse([
            'type' => 'object',
            'properties' => $schemaProperties,
        ], $className);
    }
}
