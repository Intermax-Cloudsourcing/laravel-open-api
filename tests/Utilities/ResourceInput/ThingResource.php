<?php

namespace Intermax\LaravelOpenApi\Tests\Utilities\ResourceInput;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Intermax\LaravelOpenApi\Generator\Values\BooleanValue;
use Intermax\LaravelOpenApi\Generator\Values\DateTimeValue;
use Intermax\LaravelOpenApi\Generator\Values\IntegerValue;
use Intermax\LaravelOpenApi\Generator\Values\NumberValue;
use Intermax\LaravelOpenApi\Generator\Values\StringValue;

/**
 * @property \Intermax\LaravelOpenApi\Generator\ResourceInput $resource
 */
class ThingResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<mixed>
     */
    public function toArray($request)
    {
        return [
            'type' => 'things',
            'attributes' => [
                'name' => (string) $this->resource->name, // @phpstan-ignore-line
                'exists' => new BooleanValue(fn () => $this->resource->exists), // @phpstan-ignore-line
                'description' => new StringValue(fn () => $this->resource->description), // @phpstan-ignore-line
                'numberOfSubThings' => new IntegerValue(fn () => $this->resource->test()->numberOfSubThings()), // @phpstan-ignore-line
                'fractionalNumber' => new NumberValue(fn () => $this->resource->fractionalNumber()), // @phpstan-ignore-line
                'createdAt' => new DateTimeValue(fn () => $this->resource->createdAt), // @phpstan-ignore-line
                'updatedAt' => Carbon::now(),
            ],
        ];
    }
}
