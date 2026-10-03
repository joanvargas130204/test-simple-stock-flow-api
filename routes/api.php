<?php

declare(strict_types=1);

use App\Presentation\Http\Controller\AuthController;
use App\Presentation\Http\Controller\CategoryController;
use App\Presentation\Http\Controller\HealthController;
use App\Presentation\Http\Controller\MediaController;
use App\Presentation\Http\Controller\ProductController;
use App\Presentation\Http\Controller\SaleController;
use App\Presentation\Http\Controller\SalesReportController;
use App\Presentation\Http\Middleware\JwtAuthMiddleware;
use App\Presentation\Http\Middleware\RequireAdminRoleMiddleware;
use App\Presentation\Http\ProblemDetails\EmptyErrorRenderer;
use Illuminate\Support\Facades\Route;

// E-14: GET /health (Anonymous)
Route::get('/health', [HealthController::class, 'index']);

// E-15: GET /media/{key} (Anonymous)
Route::get('/media/{key}', [MediaController::class, 'show']);

// E-01: POST /api/auth/login (Anonymous)
Route::post('/api/auth/login', [AuthController::class, 'login']);
Route::get('/api/auth/login', fn () => EmptyErrorRenderer::methodNotAllowed('POST'));

// Authenticated Routes (JwtAuthMiddleware by default)
Route::middleware([JwtAuthMiddleware::class])->group(function () {
    // E-02: POST /api/auth/register (Admin only)
    Route::post('/api/auth/register', [AuthController::class, 'register'])
        ->middleware(RequireAdminRoleMiddleware::class);

    // E-09: GET /api/categories
    Route::get('/api/categories', [CategoryController::class, 'index']);

    // Products (Authenticated)
    // E-03: GET /api/products
    Route::get('/api/products', [ProductController::class, 'index']);
    // E-04: GET /api/products/{id}
    Route::get('/api/products/{id}', [ProductController::class, 'show']);

    // Products Management (Admin only)
    Route::middleware([RequireAdminRoleMiddleware::class])->group(function () {
        // E-05: POST /api/products
        Route::post('/api/products', [ProductController::class, 'store']);
        // E-06: PUT /api/products/{id}
        Route::put('/api/products/{id}', [ProductController::class, 'update']);
        // E-07: DELETE /api/products/{id}
        Route::delete('/api/products/{id}', [ProductController::class, 'destroy']);
        // E-08: POST /api/products/{id}/image
        Route::post('/api/products/{id}/image', [ProductController::class, 'uploadImage']);
    });

    // Sales (Authenticated)
    // E-10: POST /api/sales
    Route::post('/api/sales', [SaleController::class, 'store']);
    // E-11: GET /api/sales
    Route::get('/api/sales', [SaleController::class, 'index']);
    // E-12: GET /api/sales/{id}
    Route::get('/api/sales/{id}', [SaleController::class, 'show']);

    // Reports (Authenticated)
    // E-13: GET /api/reports/sales
    Route::get('/api/reports/sales', [SalesReportController::class, 'index']);
});

// Fallback for unmatched API routes -> 404 empty (D-C7 / §2.3)
Route::fallback(fn () => EmptyErrorRenderer::notFound());
