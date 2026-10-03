<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\DTO\AuthResult;
use App\Application\Ports\Outbound\TokenGenerator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class JwtTokenGenerator implements TokenGenerator
{
    private string $signingKey;
    private int $ttlSeconds;

    public function __construct(?string $signingKey = null, int $ttlSeconds = 3600)
    {
        $this->signingKey = $signingKey ?? (string) env('JWT_SIGNING_KEY', 'simple-stock-flow-default-jwt-secret-key-32-chars!');
        $this->ttlSeconds = $ttlSeconds;
    }

    public function generateToken(string $userId, string $username, string $role): AuthResult
    {
        $now = time();
        $expiresAtTimestamp = $now + $this->ttlSeconds;
        $expiresAtIso = (new \DateTimeImmutable("@{$expiresAtTimestamp}", new \DateTimeZone('UTC')))
            ->format('Y-m-d\TH:i:s.u\+00:00');

        $jti = bin2hex(random_bytes(16));

        $payload = [
            'sub' => $userId,
            'unique_name' => $username,
            'role' => $role,
            'jti' => $jti,
            'iat' => $now,
            'exp' => $expiresAtTimestamp,
        ];

        $token = JWT::encode($payload, $this->signingKey, 'HS256');

        return new AuthResult(
            accessToken: $token,
            expiresAt: $expiresAtIso,
            username: $username,
            role: $role
        );
    }

    public function validateToken(string $token): ?array
    {
        try {
            JWT::$leeway = 30; // 30 seconds leeway per spec §1
            $decoded = JWT::decode($token, new Key($this->signingKey, 'HS256'));

            return [
                'sub' => (string) ($decoded->sub ?? ''),
                'unique_name' => (string) ($decoded->unique_name ?? ''),
                'role' => (string) ($decoded->role ?? ''),
                'jti' => (string) ($decoded->jti ?? ''),
                'exp' => (int) ($decoded->exp ?? 0),
            ];
        } catch (\Throwable) {
            return null;
        }
    }
}
