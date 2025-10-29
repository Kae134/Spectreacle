<?php

declare(strict_types=1);

namespace App\Domain\Booking\ValueObject;

use InvalidArgumentException;

final class BookingId
{
    private function __construct(private readonly int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("Booking ID must be positive");
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