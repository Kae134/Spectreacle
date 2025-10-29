<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use InvalidArgumentException;

final class UserRole
{
    private const ROLE_USER = 'ROLE_USER';
    private const ROLE_ADMIN = 'ROLE_ADMIN';
    
    private const VALID_ROLES = [
        self::ROLE_USER,
        self::ROLE_ADMIN,
    ];
    
    private function __construct(private readonly string $value)
    {
        if (!in_array($value, self::VALID_ROLES, true)) {
            throw new InvalidArgumentException("Invalid role: {$value}");
        }
    }
    
    public static function user(): self
    {
        return new self(self::ROLE_USER);
    }
    
    public static function admin(): self
    {
        return new self(self::ROLE_ADMIN);
    }
    
    public static function fromString(string $role): self
    {
        return new self($role);
    }
    
    public function toString(): string
    {
        return $this->value;
    }
    
    public function isAdmin(): bool
    {
        return $this->value === self::ROLE_ADMIN;
    }
    
    public function isUser(): bool
    {
        return $this->value === self::ROLE_USER;
    }
    
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}