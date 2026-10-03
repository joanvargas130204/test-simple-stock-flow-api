<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class DuplicateUsernameException extends BusinessRuleViolation
{
    public static function forUsername(string $username): self
    {
        return new self("El usuario '{$username}' ya existe.");
    }
}
