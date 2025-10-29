<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

final class PasswordHasher
{
    public function hash(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_DEFAULT);
    }
    
    public function verify(string $plainPassword, string $hash): bool
    {
        return password_verify($plainPassword, $hash);
    }
    
    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }
}