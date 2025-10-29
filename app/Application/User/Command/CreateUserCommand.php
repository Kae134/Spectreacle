<?php

declare(strict_types=1);

namespace App\Application\User\Command;

final class CreateUserCommand
{
    public function __construct(
        public readonly string $username,
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $role = null
    ) {
    }
}