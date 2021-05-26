<?php

namespace Intermax\LaravelOpenApi\Generator;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Responses;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;

class ResponsesCreator
{
    public function __construct(
        protected Request $request,
        protected ResourceFactory $resourceFactory
    ) {
    }

    /**
     * @param string $className
     * @return Responses<Response>
     * @throws TypeErrorException
     */
    public function createFromResource(string $className): Responses
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

        $okResponse = [
            '200' => [
                'content' => [
                    'application/vnd.api+json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => $schemaProperties,
                        ],
                    ],
                ],
            ],
        ];

        return new Responses($okResponse);
    }

    protected function createProperties(array $responseData): array
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
                $value = json_decode(json_encode($value), true);

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

    public function emptyResponse(): Responses
    {
        return new Responses([
            '200' => [
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
}
