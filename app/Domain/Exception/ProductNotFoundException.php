<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class ProductNotFoundException extends BusinessRuleViolation
{
    public static function forId(string $id): self
    {
        return new self("El producto {$id} no existe.");
    }
}
