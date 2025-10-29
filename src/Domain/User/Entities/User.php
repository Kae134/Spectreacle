<?php

declare(strict_types=1);

namespace Spectreacle\Domain\User\Entities;

class User
{
    public function __construct(
        private int $id,
        private string $username,
        private string $passwordHash,
        private string $email,
        private array $roles = ['user']
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }
}