<?php

declare(strict_types=1);

namespace App\Application\Booking\Handler;

use App\Application\Booking\Command\CreateBookingCommand;
use App\Application\Booking\DTO\BookingResponse;
use App\Domain\Booking\Service\BookingService;
use App\Domain\User\ValueObject\UserId;
use App\Domain\Show\ValueObject\ShowId;

final class CreateBookingHandler
{
    public function __construct(
        private readonly BookingService $bookingService
    ) {
    }
    
    public function handle(CreateBookingCommand $command): BookingResponse
    {
        $booking = $this->bookingService->createBooking(
            userId: UserId::fromString($command->userId),
            showId: ShowId::fromInt($command->showId),
            numberOfSeats: $command->numberOfSeats
        );
        
        return BookingResponse::fromBooking($booking);
    }
}