<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidRoleException extends BusinessRuleViolation
{
    public static function forRole(string $role): self
    {
        return new self("Rol no válido: '{$role}'.");
    }

    public static function adminNotAllowedHere(): self
    {
        return new self("Solo se pueden dar de alta vendedores. El administrador lo crea el despliegue.");
    }
}
