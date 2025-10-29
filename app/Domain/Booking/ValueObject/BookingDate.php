<?php

declare(strict_types=1);

namespace App\Domain\Booking\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

final class BookingDate
{
    private function __construct(private readonly DateTimeImmutable $date)
    {
    }
    
    public static function now(): self
    {
        return new self(new DateTimeImmutable());
    }
    
    public static function fromString(string $date): self
    {
        try {
            $dateTime = new DateTimeImmutable($date);
            return new self($dateTime);
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Invalid date format: {$date}");
        }
    }
    
    public function toDateTime(): DateTimeImmutable
    {
        return $this->date;
    }
    
    public function toString(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->date->format($format);
    }
    
    public function equals(self $other): bool
    {
        return $this->date == $other->date;
    }
}