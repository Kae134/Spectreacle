<?php

declare(strict_types=1);

namespace App\Application\Booking\DTO;

use App\Domain\Booking\Entity\Booking;

final class BookingResponse
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $userId,
        public readonly int $showId,
        public readonly string $bookingDate,
        public readonly int $numberOfSeats
    ) {
    }
    
    public static function fromBooking(Booking $booking): self
    {
        return new self(
            id: $booking->getId()?->toInt(),
            userId: $booking->getUserId()->toString(),
            showId: $booking->getShowId()->toInt(),
            bookingDate: $booking->getBookingDate()->toString(),
            numberOfSeats: $booking->getNumberOfSeats()
        );
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'show_id' => $this->showId,
            'booking_date' => $this->bookingDate,
            'number_of_seats' => $this->numberOfSeats,
        ];
    }
}