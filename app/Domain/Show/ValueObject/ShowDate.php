<?php

declare(strict_types=1);

namespace App\Domain\Show\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

final class ShowDate
{
    private function __construct(private readonly DateTimeImmutable $date)
    {
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
    
    public static function fromDateTime(DateTimeImmutable $dateTime): self
    {
        return new self($dateTime);
    }
    
    public function toDateTime(): DateTimeImmutable
    {
        return $this->date;
    }
    
    public function toString(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->date->format($format);
    }
    
    public function format(string $format): string
    {
        return $this->date->format($format);
    }
    
    public function isPast(): bool
    {
        return $this->date < new DateTimeImmutable();
    }
    
    public function isFuture(): bool
    {
        return $this->date > new DateTimeImmutable();
    }
    
    public function equals(self $other): bool
    {
        return $this->date == $other->date;
    }
}