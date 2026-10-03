<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Application\DTO\AuthResult;

interface TokenGenerator
{
    public function generateToken(string $userId, string $username, string $role): AuthResult;

    /**
     * @return array{sub: string, unique_name: string, role: string, jti: string, exp: int}|null
     */
    public function validateToken(string $token): ?array;
}
