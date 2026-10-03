<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class AuthResult
{
    public function __construct(
        public string $accessToken,
        public string $expiresAt,
        public string $username,
        public string $role,
    ) {
    }

    public function toArray(): array
    {
        return [
            'accessToken' => $this->accessToken,
            'expiresAt' => $this->expiresAt,
            'username' => $this->username,
            'role' => $this->role,
        ];
    }
}
