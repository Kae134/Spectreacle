<?php

declare(strict_types=1);

namespace Spectreacle\Domain\Show\Entities;

use DateTimeInterface;

class Reservation
{
    public function __construct(
        private int $id,
        private int $userId,
        private int $showId,
        private int $numberOfSeats,
        private float $totalPrice,
        private DateTimeInterface $reservationDate,
        private string $status = 'confirmed'
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getShowId(): int
    {
        return $this->showId;
    }

    public function getNumberOfSeats(): int
    {
        return $this->numberOfSeats;
    }

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    public function getReservationDate(): DateTimeInterface
    {
        return $this->reservationDate;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function cancel(): void
    {
        $this->status = 'cancelled';
    }

    public function getFormattedReservationDate(): string
    {
        return $this->reservationDate->format('d/m/Y à H:i');
    }

    public function getFormattedTotalPrice(): string
    {
        return number_format($this->totalPrice, 2, ',', ' ') . ' €';
    }
}