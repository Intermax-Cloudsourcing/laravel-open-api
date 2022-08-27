<?php

declare(strict_types=1);

namespace Intermax\LaravelOpenApi\Tests\QueryParameters\Utilities;

use Illuminate\Foundation\Http\FormRequest;
use Intermax\LaravelOpenApi\Contracts\HasQueryParameters;
use Intermax\LaravelOpenApi\Generator\Parameters\QueryParameter;

class ThingCollectionRequest extends FormRequest implements HasQueryParameters
{
    public function authorize(): bool
    {
        return true;
    }

    public function queryParameters(): array
    {
        return [
            new QueryParameter('filter[test]'),
            new QueryParameter(
                name: 'include',
                options: [
                    'friends',
                    'neighbours',
                ],
            ),
        ];
    }

    public function rules(): array
    {
        return [];
    }
}
