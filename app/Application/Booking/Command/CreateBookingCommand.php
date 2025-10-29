<?php

declare(strict_types=1);

namespace App\Application\Booking\Command;

final class CreateBookingCommand
{
    public function __construct(
        public readonly string $userId,
        public readonly int $showId,
        public readonly int $numberOfSeats = 1
    ) {
    }
}