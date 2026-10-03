<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Application\DTO\AuthResult;
use App\Application\DTO\RegisterSellerCommand;

interface Authenticate
{
    public function login(string $username, string $password): AuthResult;

    public function registerSeller(RegisterSellerCommand $command): string;

    public function bootstrapAdmin(string $email, string $password): void;
}
