<?php

namespace Intermax\LaravelOpenApi\Generator;

use cebe\openapi\spec\RequestBody;
use Illuminate\Foundation\Http\FormRequest;
use UnhandledMatchError;

class RequestBodyCreator
{
    public function create(FormRequest $request): ?RequestBody
    {
        $body = [];

        if (! method_exists($request, 'rules') || empty($request->rules())) {
            return null;
        }

        $rules = $this->normalizeRules($request->rules());

        $properties = [];

        foreach ($rules as $name => $fieldRules) {
            $nestedFieldNames = explode('.', (string) $name);
            $fieldName = array_pop($nestedFieldNames);

            $property = [
                $fieldName => [
                    'type' => $this->determineFieldType($fieldRules),
                ],
            ];

            if (count($nestedFieldNames) > 0) {
                $nestedProperty = [];

                foreach ($nestedFieldNames as $nestedName) {
                    if (isset($lastNestedField)) {
                        $lastNestedField['properties'][$nestedName] = [
                            'type' => 'object',
                            'properties' => [],
                        ];

                        $lastNestedField = &$lastNestedField['properties'][$nestedName];
                    } else {
                        $nestedProperty[$nestedName] = [
                            'type' => 'object',
                            'properties' => [],
                        ];

                        $lastNestedField = &$nestedProperty[$nestedName];
                    }
                }

                $lastNestedField['properties'] = array_merge($lastNestedField['properties'], $property);

                $property = $nestedProperty;

                unset($lastNestedField);
            }

            $properties = array_replace_recursive($properties, $property);
        }

        $body['content'] = [
            'application/vnd.api+json' => [
                'schema' => [
                    'type' => 'object',
                    'properties' => $properties,
                ],
            ],
        ];

        return new RequestBody($body);
    }

    /**
     * @param array<mixed> $rules
     * @return array<mixed>
     */
    protected function normalizeRules(array $rules): array
    {
        return array_map(function ($value) {
            if (! is_array($value)) {
                $value = explode('|', (string) $value);
            }

            return $value;
        }, $rules);
    }

    /**
     * @param array<mixed> $fieldRules
     * @return string
     */
    protected function determineFieldType(array $fieldRules): string
    {
        $type = 'string';

        foreach ($fieldRules as $rule) {
            if (is_string($rule)) {
                try {
                    $type = match ($rule) {
                        'digits',
                        'digits_between',
                        'numeric' => 'number',
                        'integer' => 'integer',
                        'boolean' => 'boolean',
                        'array' => 'array',
                    };
                } catch (UnhandledMatchError $e) {
                    // We want to do nothing with other rules, if nothing matched $type will remain 'string'
                }
            }
        }

        return $type;
    }
}
