<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class UnknownCategoryException extends BusinessRuleViolation
{
    public static function forId(string $id): self
    {
        return new self("La categoría {$id} no existe.");
    }
}
