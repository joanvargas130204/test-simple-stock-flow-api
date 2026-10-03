<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request;

use App\Presentation\Http\ProblemDetails\ValidationErrorRenderer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = [];
        foreach ($validator->errors()->toArray() as $field => $messages) {
            $parts = explode('.', (string) $field);
            $camelParts = array_map(function ($part) {
                return is_numeric($part) ? $part : lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', (string) $part))));
            }, $parts);
            $formattedKey = implode('.', $camelParts);

            $errors[$formattedKey] = $messages;
        }

        throw new HttpResponseException(ValidationErrorRenderer::render($errors));
    }
}
