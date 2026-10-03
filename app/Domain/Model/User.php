<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Exception\BusinessRuleViolation;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\Username;

final class User
{
    private string $id;
    private Username $username;
    private string $passwordHash;
    private Role $role;

    public function __construct(
        string $id,
        Username $username,
        string $passwordHash,
        Role $role
    ) {
        $trimmedHash = trim($passwordHash);
        if ($trimmedHash === '') {
            throw new class('El hash de contraseña no puede estar vacío.') extends BusinessRuleViolation {};
        }

        $this->id = $id;
        $this->username = $username;
        $this->passwordHash = $trimmedHash;
        $this->role = $role;
    }

    public static function create(
        string $id,
        string $username,
        string $passwordHash,
        string|Role $role
    ): self {
        $roleObj = $role instanceof Role ? $role : Role::fromString($role);
        return new self($id, Username::of($username), $passwordHash, $roleObj);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function username(): Username
    {
        return $this->username;
    }

    public function usernameValue(): string
    {
        return $this->username->value();
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function role(): Role
    {
        return $this->role;
    }

    public function roleValue(): string
    {
        return $this->role->value;
    }
}
