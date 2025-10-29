<?php

declare(strict_types=1);

namespace Spectreacle\Domain\Show\Repositories;

use Spectreacle\Domain\Show\Entities\Reservation;

interface ReservationRepositoryInterface
{
    public function findById(int $id): ?Reservation;
    public function findByUserId(int $userId): array;
    public function findByShowId(int $showId): array;
    public function save(Reservation $reservation): Reservation;
    public function create(
        int $userId,
        int $showId,
        int $numberOfSeats,
        float $totalPrice
    ): Reservation;
}