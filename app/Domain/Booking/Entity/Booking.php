<?php

declare(strict_types=1);

namespace App\Domain\Booking\Entity;

use App\Domain\Booking\ValueObject\BookingId;
use App\Domain\Booking\ValueObject\BookingDate;
use App\Domain\User\ValueObject\UserId;
use App\Domain\Show\ValueObject\ShowId;
use DomainException;

final class Booking
{
    private function __construct(
        private ?BookingId $id,
        private UserId $userId,
        private ShowId $showId,
        private BookingDate $bookingDate,
        private int $numberOfSeats
    ) {
    }
    
    public static function create(
        UserId $userId,
        ShowId $showId,
        int $numberOfSeats = 1
    ): self {
        if ($numberOfSeats <= 0) {
            throw new DomainException('Number of seats must be positive');
        }
        
        return new self(
            null,
            $userId,
            $showId,
            BookingDate::now(),
            $numberOfSeats
        );
    }
    
    public static function reconstitute(
        BookingId $id,
        UserId $userId,
        ShowId $showId,
        BookingDate $bookingDate,
        int $numberOfSeats
    ): self {
        return new self(
            $id,
            $userId,
            $showId,
            $bookingDate,
            $numberOfSeats
        );
    }
    
    // Getters
    public function getId(): ?BookingId
    {
        return $this->id;
    }
    
    public function getUserId(): UserId
    {
        return $this->userId;
    }
    
    public function getShowId(): ShowId
    {
        return $this->showId;
    }
    
    public function getBookingDate(): BookingDate
    {
        return $this->bookingDate;
    }
    
    public function getNumberOfSeats(): int
    {
        return $this->numberOfSeats;
    }
    
    // Business logic
    public function setId(BookingId $id): void
    {
        if ($this->id !== null) {
            throw new DomainException('Booking ID is already set');
        }
        $this->id = $id;
    }
    
    public function belongsToUser(UserId $userId): bool
    {
        return $this->userId->equals($userId);
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id?->toInt(),
            'user_id' => $this->userId->toString(),
            'show_id' => $this->showId->toInt(),
            'booking_date' => $this->bookingDate->toString(),
            'number_of_seats' => $this->numberOfSeats,
        ];
    }
}