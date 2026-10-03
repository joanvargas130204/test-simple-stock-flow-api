<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidCredentialsException extends BusinessRuleViolation
{
    public function __construct(string $message = 'Usuario o contraseña incorrectos.')
    {
        parent::__construct($message);
    }
}
