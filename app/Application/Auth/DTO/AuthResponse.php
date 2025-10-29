<?php

declare(strict_types=1);

namespace App\Application\Auth\DTO;

final class AuthResponse
{
    public function __construct(
        public readonly string $token,
        public readonly string $userId,
        public readonly string $username,
        public readonly string $role
    ) {
    }
    
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'user' => [
                'id' => $this->userId,
                'username' => $this->username,
                'role' => $this->role,
            ],
        ];
    }
}