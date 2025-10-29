<?php

declare(strict_types=1);

namespace App\Domain\Show\ValueObject;

use InvalidArgumentException;

final class ShowId
{
    private function __construct(private readonly int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("Show ID must be positive");
        }
    }
    
    public static function fromInt(int $id): self
    {
        return new self($id);
    }
    
    public function toInt(): int
    {
        return $this->value;
    }
    
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}