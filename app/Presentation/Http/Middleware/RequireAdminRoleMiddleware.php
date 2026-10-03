<?php

declare(strict_types=1);

namespace App\Presentation\Http\Middleware;

use App\Presentation\Http\ProblemDetails\EmptyErrorRenderer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequireAdminRoleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->attributes->get('auth_role');

        if ($role !== 'admin') {
            return EmptyErrorRenderer::forbidden();
        }

        return $next($request);
    }
}
