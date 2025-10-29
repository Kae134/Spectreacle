<?php

declare(strict_types=1);

namespace App\Application\Show\DTO;

use App\Domain\Show\Entity\Show;

final class ShowResponse
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $title,
        public readonly string $description,
        public readonly string $date,
        public readonly string $location,
        public readonly float $price,
        public readonly int $availableSeats,
        public readonly string $imageUrl
    ) {
    }
    
    public static function fromShow(Show $show): self
    {
        return new self(
            id: $show->getId()?->toInt(),
            title: $show->getTitle(),
            description: $show->getDescription(),
            date: $show->getDate()->toString(),
            location: $show->getLocation(),
            price: $show->getPrice()->toFloat(),
            availableSeats: $show->getAvailableSeats()->toInt(),
            imageUrl: $show->getImageUrl()
        );
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'date' => $this->date,
            'location' => $this->location,
            'price' => $this->price,
            'available_seats' => $this->availableSeats,
            'image_url' => $this->imageUrl,
        ];
    }
}