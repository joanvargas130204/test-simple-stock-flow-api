<?php

declare(strict_types=1);

namespace App\Presentation\Http\Middleware;

use App\Application\Ports\Outbound\TokenGenerator;
use App\Presentation\Http\ProblemDetails\EmptyErrorRenderer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class JwtAuthMiddleware
{
    public function __construct(
        private readonly TokenGenerator $tokenGenerator,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        if ($authHeader === null || ! str_starts_with($authHeader, 'Bearer ')) {
            return EmptyErrorRenderer::unauthorized('Bearer');
        }

        $token = substr($authHeader, 7);
        $claims = $this->tokenGenerator->validateToken($token);

        if ($claims === null || empty($claims['sub']) || empty($claims['unique_name'])) {
            return EmptyErrorRenderer::unauthorized('Bearer error="invalid_token"');
        }

        $request->attributes->set('auth_user_id', $claims['sub']);
        $request->attributes->set('auth_username', $claims['unique_name']);
        $request->attributes->set('auth_role', $claims['role']);

        return $next($request);
    }
}
