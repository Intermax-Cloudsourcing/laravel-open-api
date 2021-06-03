<?php

namespace Intermax\LaravelOpenApi\Tests\Utilities\Mapping;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Intermax\LaravelOpenApi\Generator\Attributes\MapsFields;
use Intermax\LaravelOpenApi\Generator\ResourceInput;

/**
 * @property ResourceInput $resource
 */
#[MapsFields(ThingMapper::class)]
class ThingResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<mixed>
     */
    public function toArray($request)
    {
        return [
            'type' => 'things',
            'attributes' => [
                'name' => (string) $this->resource->name, // @phpstan-ignore-line
                'exists' => (string) $this->resource->exists, // @phpstan-ignore-line
                'numberOfSubThings' => (int) $this->resource->numberOfSubThings(), // @phpstan-ignore-line
                'fractionalNumber' => (float) $this->resource->fractionalNumber(), // @phpstan-ignore-line
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ],
        ];
    }
}
