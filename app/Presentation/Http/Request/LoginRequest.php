<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request;

final class LoginRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }
}
