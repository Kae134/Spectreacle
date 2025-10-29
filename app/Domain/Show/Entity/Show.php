<?php

declare(strict_types=1);

namespace App\Domain\Show\Entity;

use App\Domain\Show\ValueObject\ShowId;
use App\Domain\Show\ValueObject\ShowDate;
use App\Domain\Show\ValueObject\Price;
use App\Domain\Show\ValueObject\SeatsAvailability;
use DomainException;

final class Show
{
    private function __construct(
        private ?ShowId $id,
        private string $title,
        private string $description,
        private ShowDate $date,
        private string $location,
        private Price $price,
        private SeatsAvailability $availableSeats,
        private string $imageUrl
    ) {
    }
    
    public static function create(
        string $title,
        string $description,
        ShowDate $date,
        string $location,
        Price $price,
        SeatsAvailability $availableSeats,
        string $imageUrl = ''
    ): self {
        if (empty($title)) {
            throw new DomainException('Show title cannot be empty');
        }
        
        if (empty($location)) {
            throw new DomainException('Show location cannot be empty');
        }
        
        return new self(
            null,
            $title,
            $description,
            $date,
            $location,
            $price,
            $availableSeats,
            $imageUrl ?: 'https://via.placeholder.com/400x300'
        );
    }
    
    public static function reconstitute(
        ShowId $id,
        string $title,
        string $description,
        ShowDate $date,
        string $location,
        Price $price,
        SeatsAvailability $availableSeats,
        string $imageUrl
    ): self {
        return new self(
            $id,
            $title,
            $description,
            $date,
            $location,
            $price,
            $availableSeats,
            $imageUrl
        );
    }
    
    // Getters
    public function getId(): ?ShowId
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
    
    public function getDate(): ShowDate
    {
        return $this->date;
    }
    
    public function getLocation(): string
    {
        return $this->location;
    }
    
    public function getPrice(): Price
    {
        return $this->price;
    }
    
    public function getAvailableSeats(): SeatsAvailability
    {
        return $this->availableSeats;
    }
    
    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }
    
    // Business logic
    public function setId(ShowId $id): void
    {
        if ($this->id !== null) {
            throw new DomainException('Show ID is already set');
        }
        $this->id = $id;
    }
    
    public function hasAvailableSeats(): bool
    {
        return $this->availableSeats->hasAvailability();
    }
    
    public function canBook(int $requestedSeats = 1): bool
    {
        return $this->availableSeats->canBook($requestedSeats);
    }
    
    public function bookSeats(int $numberOfSeats = 1): void
    {
        if (!$this->canBook($numberOfSeats)) {
            throw new DomainException('Not enough seats available');
        }
        
        $this->availableSeats = $this->availableSeats->decrement($numberOfSeats);
    }
    
    public function isPast(): bool
    {
        return $this->date->isPast();
    }
    
    public function isFuture(): bool
    {
        return $this->date->isFuture();
    }
    
    public function updateDetails(
        string $title,
        string $description,
        ShowDate $date,
        string $location,
        Price $price
    ): void {
        if (empty($title)) {
            throw new DomainException('Show title cannot be empty');
        }
        
        if (empty($location)) {
            throw new DomainException('Show location cannot be empty');
        }
        
        $this->title = $title;
        $this->description = $description;
        $this->date = $date;
        $this->location = $location;
        $this->price = $price;
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id?->toInt(),
            'title' => $this->title,
            'description' => $this->description,
            'date' => $this->date->toString(),
            'location' => $this->location,
            'price' => $this->price->toFloat(),
            'available_seats' => $this->availableSeats->toInt(),
            'image_url' => $this->imageUrl,
        ];
    }
}