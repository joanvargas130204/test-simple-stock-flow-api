<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\DTO\RegisterSellerCommand;
use App\Application\Ports\Inbound\Authenticate;
use App\Domain\Exception\BusinessRuleViolation;
use App\Presentation\Http\ProblemDetails\ProblemDetailsRenderer;
use App\Presentation\Http\Request\LoginRequest;
use App\Presentation\Http\Request\RegisterSellerRequest;
use Illuminate\Http\JsonResponse;

final class AuthController
{
    public function __construct(
        private readonly Authenticate $authenticate,
    ) {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authenticate->login(
                (string) $request->input('username', ''),
                (string) $request->input('password', '')
            );

            return response()->json($result->toArray(), 200);
        } catch (BusinessRuleViolation $e) {
            return ProblemDetailsRenderer::ruleViolation($e->getMessage());
        }
    }

    public function register(RegisterSellerRequest $request): JsonResponse
    {
        try {
            $userId = $this->authenticate->registerSeller(new RegisterSellerCommand(
                username: (string) $request->input('username', ''),
                password: (string) $request->input('password', ''),
                role: (string) $request->input('role', '')
            ));

            return response()->json(['id' => $userId], 201);
        } catch (BusinessRuleViolation $e) {
            return ProblemDetailsRenderer::ruleViolation($e->getMessage());
        }
    }
}
