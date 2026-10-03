<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class RegisterSellerCommand
{
    public function __construct(
        public string $username,
        public string $password,
        public string $role,
    ) {
    }
}
