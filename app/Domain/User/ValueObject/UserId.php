<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use InvalidArgumentException;

final class UserId
{
    private function __construct(private readonly string $value)
    {
        if (!$this->isValidUuid($value)) {
            throw new InvalidArgumentException("Invalid UUID format: {$value}");
        }
    }
    
    public static function generate(): self
    {
        return new self(self::generateUuid());
    }
    
    public static function fromString(string $uuid): self
    {
        return new self($uuid);
    }
    
    public function toString(): string
    {
        return $this->value;
    }
    
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
    
    private function isValidUuid(string $uuid): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid);
    }
    
    private static function generateUuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}