<?php

declare(strict_types=1);

namespace App\Application\Auth\Command;

final class LoginCommand
{
    public function __construct(
        public readonly string $username,
        public readonly string $password
    ) {
    }
}