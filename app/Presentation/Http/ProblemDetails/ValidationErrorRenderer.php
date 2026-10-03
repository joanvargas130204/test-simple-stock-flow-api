<?php

declare(strict_types=1);

namespace App\Presentation\Http\ProblemDetails;

use Illuminate\Http\JsonResponse;

final class ValidationErrorRenderer
{
    /**
     * @param array<string, array<string>> $errors
     */
    public static function render(array $errors): JsonResponse
    {
        $fields = array_keys($errors);
        $fieldsList = implode(', ', $fields);
        $detail = "Datos de entrada no válidos: {$fieldsList}.";

        return response()->json([
            'title' => 'Datos de entrada no válidos',
            'status' => 400,
            'detail' => $detail,
            'errors' => $errors,
        ], 400, [
            'Content-Type' => 'application/problem+json; charset=utf-8',
        ]);
    }
}
