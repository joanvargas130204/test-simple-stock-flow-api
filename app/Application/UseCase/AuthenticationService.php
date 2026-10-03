<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\AuthResult;
use App\Application\DTO\RegisterSellerCommand;
use App\Application\Ports\Inbound\Authenticate;
use App\Application\Ports\Outbound\PasswordHasher;
use App\Application\Ports\Outbound\TokenGenerator;
use App\Application\Ports\Outbound\UserRepository;
use App\Domain\Exception\BusinessRuleViolation;
use App\Domain\Exception\DuplicateUsernameException;
use App\Domain\Exception\InvalidCredentialsException;
use App\Domain\Exception\InvalidRoleException;
use App\Domain\Model\User;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\Username;

final readonly class AuthenticationService implements Authenticate
{
    public function __construct(
        private UserRepository $userRepository,
        private PasswordHasher $passwordHasher,
        private TokenGenerator $tokenGenerator,
    ) {
    }

    public function login(string $username, string $password): AuthResult
    {
        if ($username === '' || $password === '') {
            throw new InvalidCredentialsException('Usuario o contraseña incorrectos.');
        }

        $normalized = Username::normalize($username);
        $user = $this->userRepository->findByUsername($normalized);

        if ($user === null) {
            throw new InvalidCredentialsException('Usuario o contraseña incorrectos.');
        }

        if (! $this->passwordHasher->verify($password, $user->passwordHash)) {
            throw new InvalidCredentialsException('Usuario o contraseña incorrectos.');
        }

        return $this->tokenGenerator->generateToken(
            $user->id,
            $user->username->value,
            $user->role->value
        );
    }

    public function registerSeller(RegisterSellerCommand $command): string
    {
        // 1. DP-04 check: "admin" is rejected before looking up existing users
        if ($command->role === 'admin') {
            throw new BusinessRuleViolation('Solo se pueden dar de alta vendedores. El administrador lo crea el despliegue.');
        }

        // 2. Check if username is already taken
        $normalized = Username::normalize($command->username);
        if ($this->userRepository->existsByUsername($normalized)) {
            throw new DuplicateUsernameException("El usuario '{$command->username}' ya existe.");
        }

        // 3. Check role valid for seller registration
        if ($command->role !== 'seller') {
            throw new InvalidRoleException("Rol no válido: '{$command->role}'.");
        }

        $userId = self::generateUuid();
        $passwordHash = $this->passwordHasher->hash($command->password);

        $user = new User(
            id: $userId,
            username: new Username($command->username),
            passwordHash: $passwordHash,
            role: Role::from($command->role)
        );

        $this->userRepository->save($user);

        return $user->id;
    }

    public function bootstrapAdmin(string $email, string $password): void
    {
        $normalized = Username::normalize($email);
        if (! $this->userRepository->existsByUsername($normalized)) {
            $user = new User(
                id: self::generateUuid(),
                username: new Username($normalized),
                passwordHash: $this->passwordHasher->hash($password),
                role: Role::ADMIN
            );
            $this->userRepository->save($user);
        }
    }

    private static function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40); // version 4
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80); // variant RFC 4122

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
