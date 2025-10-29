<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use InvalidArgumentException;

final class Email
{
    private function __construct(private readonly string $value)
    {
        if (!$this->isValid($value)) {
            throw new InvalidArgumentException("Invalid email format: {$value}");
        }
    }
    
    public static function fromString(string $email): self
    {
        return new self(strtolower(trim($email)));
    }
    
    public function toString(): string
    {
        return $this->value;
    }
    
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
    
    private function isValid(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}