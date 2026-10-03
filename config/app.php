<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'Simple Stock Flow'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost:8000'),
    'timezone' => 'UTC',
    'locale' => 'es',
    'fallback_locale' => 'en',
    'key' => env('APP_KEY', 'base64:4Z8X31k3O8eB3+Z8+BwN8Qj8Z8+BwN8Qj8Z8+BwN8Qg='),
    'cipher' => 'AES-256-CBC',
];
