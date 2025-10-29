<?php

declare(strict_types=1);

namespace App\Application\User\DTO;

use App\Domain\User\Entity\User;

final class UserResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $username,
        public readonly string $email,
        public readonly string $role
    ) {
    }
    
    public static function fromUser(User $user): self
    {
        return new self(
            id: $user->getId()->toString(),
            username: $user->getUsername(),
            email: $user->getEmail()->toString(),
            role: $user->getRole()->toString()
        );
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
        ];
    }
}