<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidRoleException;

enum Role: string
{
    case ADMIN = 'admin';
    case SELLER = 'seller';

    public static function fromString(string $role): self
    {
        return match (trim($role)) {
            'admin' => self::ADMIN,
            'seller' => self::SELLER,
            default => throw InvalidRoleException::forRole($role),
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function isSeller(): bool
    {
        return $this === self::SELLER;
    }
}
