<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request;

final class PlaceSaleRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'lines' => ['required', 'array'],
            'lines.*.productId' => ['required', 'string'],
            'lines.*.quantity' => ['required', 'integer'],
        ];
    }
}
