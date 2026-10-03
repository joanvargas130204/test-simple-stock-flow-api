<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Ports\Outbound\PasswordHasher;

final class Argon2PasswordHasher implements PasswordHasher
{
    public function hash(string $plainPassword): string
    {
        // Use Argon2id if available, fallback to Argon2i or default secure algorithm
        $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : (defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT);

        return password_hash($plainPassword, $algo);
    }

    public function verify(string $plainPassword, string $hash): bool
    {
        return password_verify($plainPassword, $hash);
    }
}
