<?php

declare(strict_types=1);

namespace Spectreacle\Domain\Show\Repositories;

use Spectreacle\Domain\Show\Entities\Show;

interface ShowRepositoryInterface
{
    public function findById(int $id): ?Show;
    public function findAll(): array;
    public function findByCategory(string $category): array;
    public function findAvailable(): array;
    public function save(Show $show): Show;
    public function create(
        string $title,
        string $description,
        string $category,
        \DateTimeInterface $dateTime,
        string $venue,
        int $totalSeats,
        float $price,
        string $imageUrl = ''
    ): Show;
}