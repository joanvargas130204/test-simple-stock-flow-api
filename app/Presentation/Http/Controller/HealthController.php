<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use Illuminate\Http\JsonResponse;

final class HealthController
{
    public function index(): JsonResponse
    {
        return response()->json(['status' => 'ok'], 200);
    }
}
