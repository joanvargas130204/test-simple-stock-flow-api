<?php

declare(strict_types=1);

namespace App\Presentation\Http\ProblemDetails;

use Illuminate\Http\Response;

final class EmptyErrorRenderer
{
    public static function unauthorized(?string $authHeader = 'Bearer'): Response
    {
        return response('', 401, [
            'Content-Length' => '0',
            'WWW-Authenticate' => $authHeader ?? 'Bearer',
        ]);
    }

    public static function forbidden(): Response
    {
        return response('', 403, [
            'Content-Length' => '0',
        ]);
    }

    public static function notFound(): Response
    {
        return response('', 404, [
            'Content-Length' => '0',
        ]);
    }

    public static function methodNotAllowed(string $allow = 'POST'): Response
    {
        return response('', 405, [
            'Content-Length' => '0',
            'Allow' => $allow,
        ]);
    }
}
