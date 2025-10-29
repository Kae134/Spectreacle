<?php

declare(strict_types=1);

namespace Spectreacle\Domain\Show\Entities;

use DateTimeInterface;

class Show
{
    public function __construct(
        private int $id,
        private string $title,
        private string $description,
        private string $category,
        private DateTimeInterface $dateTime,
        private string $venue,
        private int $totalSeats,
        private int $availableSeats,
        private float $price,
        private string $imageUrl = ''
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getDateTime(): DateTimeInterface
    {
        return $this->dateTime;
    }

    public function getVenue(): string
    {
        return $this->venue;
    }

    public function getTotalSeats(): int
    {
        return $this->totalSeats;
    }

    public function getAvailableSeats(): int
    {
        return $this->availableSeats;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    public function isAvailable(): bool
    {
        return $this->availableSeats > 0 && $this->dateTime > new \DateTime();
    }

    public function canReserveSeats(int $numberOfSeats): bool
    {
        return $this->isAvailable() && $this->availableSeats >= $numberOfSeats;
    }

    public function reserveSeats(int $numberOfSeats): void
    {
        if (!$this->canReserveSeats($numberOfSeats)) {
            throw new \DomainException('Impossible de réserver ce nombre de places');
        }

        $this->availableSeats -= $numberOfSeats;
    }

    public function getFormattedDate(): string
    {
        return $this->dateTime->format('d/m/Y à H:i');
    }

    public function getFormattedPrice(): string
    {
        return number_format($this->price, 2, ',', ' ') . ' €';
    }
}