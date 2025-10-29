<?php

declare(strict_types=1);

namespace App\Domain\Show\ValueObject;

use InvalidArgumentException;

final class Price
{
    private function __construct(private readonly float $amount)
    {
        if ($amount < 0) {
            throw new InvalidArgumentException("Price cannot be negative");
        }
    }
    
    public static function fromFloat(float $amount): self
    {
        return new self(round($amount, 2));
    }
    
    public function toFloat(): float
    {
        return $this->amount;
    }
    
    public function format(): string
    {
        return number_format($this->amount, 2, ',', ' ') . ' €';
    }
    
    public function equals(self $other): bool
    {
        return abs($this->amount - $other->amount) < 0.01;
    }
}