<?php

namespace Intermax\LaravelOpenApi\Generator;

use cebe\openapi\spec\RequestBody;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Intermax\LaravelOpenApi\Contracts\HasQueryParameters;
use UnhandledMatchError;

class RequestBodyCreator
{
    public function __construct(private readonly Repository $config)
    {
    }

    public function create(FormRequest $request): ?RequestBody
    {
        $body = [];

        if (! method_exists($request, 'rules')) {
            return null;
        }

        $rules = $this->rulesWithoutQueryParameters($request);

        if (empty($rules)) {
            return null;
        }

        $rules = $this->normalizeRules($rules);

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
            $this->config->get('open-api.content_type') => [
                'schema' => [
                    'type' => 'object',
                    'properties' => $properties,
                ],
            ],
        ];

        return new RequestBody($body);
    }

    /**
     * @param  array<mixed>  $rules
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
     * @param  array<mixed>  $fieldRules
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

    /**
     * @return array<string, array<string|Rule>|string|Rule>
     */
    protected function rulesWithoutQueryParameters(FormRequest $request): array
    {
        $queryParameters = [];

        if ($request instanceof HasQueryParameters) {
            foreach ($request->queryParameters() as $parameter) {
                $queryParameters[] = str_replace(
                    '[', '.', str_replace(
                        ']', '', $parameter->name()
                    )
                );
            }
        }

        assert(method_exists($request, 'rules'));

        return array_filter(
            array: $request->rules(),
            callback: fn ($name) => ! in_array($name, $queryParameters),
            mode: ARRAY_FILTER_USE_KEY
        );
    }
}
