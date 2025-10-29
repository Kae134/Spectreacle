<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Database;

use Spectreacle\Domain\Show\Entities\Reservation;
use Spectreacle\Domain\Show\Repositories\ReservationRepositoryInterface;
use DateTime;

class FileReservationRepository implements ReservationRepositoryInterface
{
    private array $reservations = [];
    private int $nextId = 1;
    private string $dataFile;

    public function __construct()
    {
        $this->dataFile = sys_get_temp_dir() . '/spectreacle_reservations.json';
        $this->loadReservations();
    }

    private function loadReservations(): void
    {
        if (file_exists($this->dataFile)) {
            $data = json_decode(file_get_contents($this->dataFile), true);
            if ($data && isset($data['reservations']) && isset($data['nextId'])) {
                $this->nextId = $data['nextId'];
                foreach ($data['reservations'] as $resData) {
                    $this->reservations[] = new Reservation(
                        $resData['id'],
                        $resData['userId'],
                        $resData['showId'],
                        $resData['numberOfSeats'],
                        $resData['totalPrice'],
                        new DateTime($resData['reservationDate']),
                        $resData['status']
                    );
                }
            }
        }
    }

    private function saveReservations(): void
    {
        $data = [
            'nextId' => $this->nextId,
            'reservations' => []
        ];
        
        foreach ($this->reservations as $reservation) {
            $data['reservations'][] = [
                'id' => $reservation->getId(),
                'userId' => $reservation->getUserId(),
                'showId' => $reservation->getShowId(),
                'numberOfSeats' => $reservation->getNumberOfSeats(),
                'totalPrice' => $reservation->getTotalPrice(),
                'reservationDate' => $reservation->getReservationDate()->format('Y-m-d H:i:s'),
                'status' => $reservation->getStatus()
            ];
        }
        
        file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT));
    }

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
                $this->saveReservations();
                return $reservation;
            }
        }
        
        $this->reservations[] = $reservation;
        $this->saveReservations();
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
        $this->saveReservations();
        return $reservation;
    }
}