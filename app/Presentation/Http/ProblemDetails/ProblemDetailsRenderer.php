<?php

declare(strict_types=1);

namespace App\Presentation\Http\ProblemDetails;

use Illuminate\Http\JsonResponse;

final class ProblemDetailsRenderer
{
    public static function ruleViolation(string $detail): JsonResponse
    {
        return response()->json([
            'title' => 'Regla de negocio violada',
            'status' => 422,
            'detail' => $detail,
        ], 422, [
            'Content-Type' => 'application/problem+json; charset=utf-8',
        ]);
    }

    public static function concurrencyConflict(?string $detail = null): JsonResponse
    {
        return response()->json([
            'title' => 'Conflicto con otra operación simultánea',
            'status' => 409,
            'detail' => $detail ?? 'Otra operación modificó los datos al mismo tiempo. Inténtalo de nuevo.',
        ], 409, [
            'Content-Type' => 'application/problem+json; charset=utf-8',
        ]);
    }

    public static function internalServerError(): JsonResponse
    {
        return response()->json([
            'title' => 'Error interno',
            'status' => 500,
            'detail' => 'Ocurrió un error inesperado.',
        ], 500, [
            'Content-Type' => 'application/problem+json; charset=utf-8',
        ]);
    }
}
