<?php

declare(strict_types=1);

use App\Application\Exception\ConcurrencyConflict;
use App\Domain\Exception\BusinessRuleViolation;
use App\Presentation\Http\ProblemDetails\EmptyErrorRenderer;
use App\Presentation\Http\ProblemDetails\ProblemDetailsRenderer;
use App\Presentation\Http\ProblemDetails\ValidationErrorRenderer;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware configuration if needed
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 1. Validation error -> 400
        $exceptions->render(function (ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $field => $messages) {
                $parts = explode('.', (string) $field);
                $camelParts = array_map(function ($part) {
                    return is_numeric($part) ? $part : lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', (string) $part))));
                }, $parts);
                $formattedKey = implode('.', $camelParts);
                $errors[$formattedKey] = $messages;
            }
            return ValidationErrorRenderer::render($errors);
        });

        // 2. Concurrency Conflict -> 409
        $exceptions->render(function (ConcurrencyConflict $e) {
            return ProblemDetailsRenderer::concurrencyConflict($e->getMessage());
        });

        // 3. Business Rule Violation -> 422
        $exceptions->render(function (BusinessRuleViolation $e) {
            return ProblemDetailsRenderer::ruleViolation($e->getMessage());
        });

        // 4. Empty responses for 401, 403, 404, 405
        $exceptions->render(function (NotFoundHttpException $e) {
            return EmptyErrorRenderer::notFound();
        });

        $exceptions->render(function (UnauthorizedHttpException $e) {
            return EmptyErrorRenderer::unauthorized();
        });

        $exceptions->render(function (AccessDeniedHttpException $e) {
            return EmptyErrorRenderer::forbidden();
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e) {
            return EmptyErrorRenderer::methodNotAllowed(implode(', ', $e->getHeaders()['Allow'] ?? ['POST']));
        });

        $exceptions->render(function (HttpExceptionInterface $e) {
            $status = $e->getStatusCode();
            return match ($status) {
                401 => EmptyErrorRenderer::unauthorized(),
                403 => EmptyErrorRenderer::forbidden(),
                404 => EmptyErrorRenderer::notFound(),
                405 => EmptyErrorRenderer::methodNotAllowed(),
                default => ProblemDetailsRenderer::internalServerError(),
            };
        });

        // 5. Unhandled general exception -> 500
        $exceptions->render(function (\Throwable $e) {
            return ProblemDetailsRenderer::internalServerError();
        });
    })->create();
