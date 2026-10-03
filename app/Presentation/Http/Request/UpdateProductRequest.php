<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request;

final class UpdateProductRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'price' => ['required', 'numeric'],
            'stock' => ['required', 'integer'],
            'categoryId' => ['required', 'string'],
        ];
    }
}
