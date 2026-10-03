<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Exception\BusinessRuleViolation;

final class Category
{
    private string $id;
    private string $name;

    public function __construct(string $id, string $name)
    {
        $trimmedName = trim($name);
        if ($trimmedName === '') {
            throw new class('El nombre de la categoría no puede estar vacío.') extends BusinessRuleViolation {};
        }

        $this->id = $id;
        $this->name = $trimmedName;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function rename(string $newName): void
    {
        $trimmed = trim($newName);
        if ($trimmed === '') {
            throw new class('El nombre de la categoría no puede estar vacío.') extends BusinessRuleViolation {};
        }
        $this->name = $trimmed;
    }
}
