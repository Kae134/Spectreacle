<?php

declare(strict_types=1);

namespace App\Application\Show\Command;

final class CreateShowCommand
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $date,
        public readonly string $location,
        public readonly float $price,
        public readonly int $availableSeats,
        public readonly string $imageUrl = ''
    ) {
    }
}