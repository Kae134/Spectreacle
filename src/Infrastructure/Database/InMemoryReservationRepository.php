<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Database;

use Spectreacle\Domain\Show\Entities\Reservation;
use Spectreacle\Domain\Show\Repositories\ReservationRepositoryInterface;
use DateTime;

class InMemoryReservationRepository implements ReservationRepositoryInterface
{
    private array $reservations = [];
    private int $nextId = 1;

    public function findById(int $id): ?Reservation
    {
        foreach ($this->reservations as $reservation) {
            if ($reservation->getId() === $id) {
                return $reservation;
            }
        }
        return null;
    }

    public function findByUserId(int $userId): array
    {
        return array_filter(
            $this->reservations,
            fn($reservation) => $reservation->getUserId() === $userId
        );
    }

    public function findByShowId(int $showId): array
    {
        return array_filter(
            $this->reservations,
            fn($reservation) => $reservation->getShowId() === $showId
        );
    }

    public function save(Reservation $reservation): Reservation
    {
        // Pour la simplicité, on remplace la réservation si elle existe déjà
        foreach ($this->reservations as $index => $existingReservation) {
            if ($existingReservation->getId() === $reservation->getId()) {
                $this->reservations[$index] = $reservation;
                return $reservation;
            }
        }
        
        $this->reservations[] = $reservation;
        return $reservation;
    }

    public function create(
        int $userId,
        int $showId,
        int $numberOfSeats,
        float $totalPrice
    ): Reservation {
        $reservation = new Reservation(
            $this->nextId++,
            $userId,
            $showId,
            $numberOfSeats,
            $totalPrice,
            new DateTime()
        );

        $this->reservations[] = $reservation;
        return $reservation;
    }
}