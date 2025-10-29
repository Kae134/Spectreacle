<?php

declare(strict_types=1);

namespace App\Domain\Show\ValueObject;

use InvalidArgumentException;

final class SeatsAvailability
{
    private function __construct(private readonly int $availableSeats)
    {
        if ($availableSeats < 0) {
            throw new InvalidArgumentException("Available seats cannot be negative");
        }
    }
    
    public static function fromInt(int $seats): self
    {
        return new self($seats);
    }
    
    public function toInt(): int
    {
        return $this->availableSeats;
    }
    
    public function hasAvailability(): bool
    {
        return $this->availableSeats > 0;
    }
    
    public function canBook(int $requestedSeats): bool
    {
        return $this->availableSeats >= $requestedSeats;
    }
    
    public function decrement(int $seats = 1): self
    {
        if ($seats > $this->availableSeats) {
            throw new InvalidArgumentException("Cannot decrement more seats than available");
        }
        
        return new self($this->availableSeats - $seats);
    }
    
    public function equals(self $other): bool
    {
        return $this->availableSeats === $other->availableSeats;
    }
}