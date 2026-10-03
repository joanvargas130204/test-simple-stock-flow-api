<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\BusinessRuleViolation;

final readonly class Username
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = mb_strtolower(trim($value));
        if ($normalized === '') {
            throw new class('El nombre de usuario no puede estar vacío.') extends BusinessRuleViolation {};
        }

        $this->value = $normalized;
    }

    public static function of(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
