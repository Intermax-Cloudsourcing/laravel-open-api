<?php

namespace Intermax\LaravelOpenApi\Generator;

use Intermax\LaravelOpenApi\Contracts\Filter;
use Intermax\LaravelOpenApi\Contracts\FilterRequest;
use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Responses;

class OperationCreator
{
    public function create(string $action, string $uri, string $entityName): Operation
    {
        $createMethod = 'create' . ucfirst($action) . 'Operation';

        return $this->$createMethod(ucfirst($entityName), $uri);
    }

    /**
     * @param string $entity
     * @param string $uri
     * @return Operation
     * @throws TypeErrorException
     */
    public function createIndexOperation(string $entity, string $uri)
    {
        $filterRequestClass = "\\App\\Http\\Requests\\{$entity}CollectionRequest";
        $filterParameters = [];
        if (class_exists($filterRequestClass)) {
            /** @var FilterRequest $filterRequest */
            $filterRequest = new $filterRequestClass();


            /** @var Filter $filter */
            foreach ($filterRequest->filters() as $filter) {
                foreach ($filter->parameters() as $parameter => $type) {
                    $filterParameters[] = new Parameter([
                        'name' => $parameter,
                        'in' => 'query',
                        'required' => false,
                        'schema' => [
                            'type' => $type
                        ]
                    ]);
                }
            }
        }

        return new Operation([
            'tags' => [
                $entity
            ],
            'operationId' => "get{$entity}Collection",
            'summary' => "Get a collection of {$entity} resources",
            'parameters' => array_merge([
                new Parameter([
                    'name' => 'page',
                    'in' => 'query',
                    'required' => false,
                    'description' => 'The collection page number',
                    'schema' => [
                        'type' => 'integer',
                        'default' => 1
                    ]
                ])
            ], $filterParameters),
            'responses' => new Responses([
                200 => new Response([
                    'description' => "{$entity} collection response",
                    'content' => [
                        'application/ld+json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'hydra:member' => [
                                        'type' => 'array',
                                        'items' => [
                                            '$ref' => "#components/schemas/{$entity}"
                                        ]
                                    ],
                                    'hydra:totalItems' => [
                                        'type' => 'integer',
                                        'minimum' => 0
                                    ],
                                    'hydra:view' => [
                                        'type' => 'object',
                                        'properties' => [
                                            '@id' => [
                                                'type' => 'string',
                                                'format' => 'iri-reference'
                                            ],
                                            '@type' => [
                                                'type' => 'string'
                                            ],
                                            'hydra:first' => [
                                                'type' => 'string',
                                                'format' => 'iri-reference'
                                            ],
                                            'hydra:last' => [
                                                'type' => 'string',
                                                'format' => 'iri-reference'
                                            ],
                                            'hydra:next' => [
                                                'type' => 'string',
                                                'format' => 'iri-reference'
                                            ]
                                        ]
                                    ]
                                ],
                                'required' => [
                                    'hydra:member'
                                ]
                            ]
                        ]
                    ]
                ])
            ])
        ]);
    }

    /**
     * @param string $entity
     * @param string $uri
     * @return Operation
     * @throws TypeErrorException
     */
    public function createShowOperation(string $entity, string $uri)
    {
        return new Operation([
            'tags' => [
                $entity
            ],
            'operationId' => "get{$entity}Item",
            'summary' => "Retrieves a {$entity} resource",
            'parameters' => [
                new Parameter([
                    'name' => strtolower($entity),
                    'description' => "The {$entity} id.",
                    'in' => 'path',
                    'required' => true,
                    'schema' => [
                        'type' => 'integer',
                        'default' => 1
                    ]
                ])
            ],
            'responses' => new Responses([
                '200' => new Response([
                    'description' => "{$entity} resource response",
                    'content' => [
                        'application/ld+json' => [
                            'schema' => [
                                '$ref' => "#/components/schemas/{$entity}"
                            ]
                        ]
                    ]
                ]),
                '404' => [
                    'description' => 'Resource not found'
                ]
            ])
        ]);
    }

    /**
     * @param string $entity
     * @param string $uri
     * @return Operation
     * @throws TypeErrorException
     */
    public function createStoreOperation(string $entity, string $uri)
    {
        return new Operation([
            'tags' => [
                $entity
            ],
            'operationId' => "post{$entity}Collection",
            'summary' => "Creates a {$entity} resource",
            'responses' => new Responses([
                201 => new Response([
                    'description' => "{$entity} resource created",
                    'content' => [
                        'application/ld+json' => [
                            'schema' => [
                                '$ref' => "#/components/schemas/{$entity}"
                            ]
                        ]
                    ]
                ])
            ]),
            'requestBody' => new RequestBody([
                'content' => [
                    'application/ld+json' => [
                        'schema' => [
                            '$ref' => "#/components/schemas/{$entity}"
                        ]
                    ]
                ]
            ]),
        ]);
    }

    /**
     * @param string $entity
     * @param string $uri
     * @return Operation
     * @throws TypeErrorException
     */
    public function createUpdateOperation(string $entity, string $uri)
    {
        return new Operation([
            'tags' => [
                $entity
            ],
            'operationId' => "update{$entity}Item",
            'summary' => "Update a {$entity} resource",
            'parameters' => [
                new Parameter([
                    'name' => strtolower($entity),
                    'description' => "The {$entity} id.",
                    'in' => 'path',
                    'required' => true,
                    'schema' => [
                        'type' => 'integer',
                        'default' => 1
                    ]
                ])
            ],
            'responses' => new Responses([
                200 => new Response([
                    'description' => "{$entity} resource response",
                    'content' => [
                        'application/ld+json' => [
                            'schema' => [
                                '$ref' => "#/components/schemas/{$entity}"
                            ]
                        ]
                    ]
                ])
            ]),
            'requestBody' => new RequestBody([
                'content' => [
                    'application/ld+json' => [
                        'schema' => [
                            '$ref' => "#/components/schemas/{$entity}"
                        ]
                    ]
                ]
            ]),
        ]);
    }

    /**
     * @param string $entity
     * @param string $uri
     * @return Operation
     * @throws TypeErrorException
     */
    public function createDestroyOperation(string $entity, string $uri)
    {
        return new Operation([
            'tags' => [
                $entity
            ],
            'operationId' => "delete{$entity}Item",
            'summary' => "Deletes a {$entity} resource",
            'parameters' => [
                new Parameter([
                    'name' => strtolower($entity),
                    'description' => "The {$entity} id.",
                    'in' => 'path',
                    'required' => true,
                    'schema' => [
                        'type' => 'integer',
                        'default' => 1
                    ]
                ])
            ],
            'responses' => new Responses([
                200 => new Response([
                    'description' => "{$entity} delete resource response",
                    'content' => []
                ])
            ])
        ]);
    }
}
