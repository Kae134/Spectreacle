<?php

declare(strict_types=1);

namespace App\Domain\Booking\Service;

use App\Domain\Booking\Entity\Booking;
use App\Domain\Booking\Repository\BookingRepositoryInterface;
use App\Domain\Show\Repository\ShowRepositoryInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\UserId;
use App\Domain\Show\ValueObject\ShowId;
use App\Domain\Show\Service\ShowService;
use DomainException;

final class BookingService
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly ShowRepositoryInterface $showRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly ShowService $showService
    ) {
    }
    
    public function createBooking(
        UserId $userId,
        ShowId $showId,
        int $numberOfSeats = 1
    ): Booking {
        // Vérifier que l'utilisateur existe
        if (!$this->userRepository->exists($userId)) {
            throw new DomainException('User not found');
        }
        
        // Vérifier que le spectacle existe et est réservable
        $show = $this->showService->ensureShowExists($showId);
        $this->showService->ensureShowIsBookable($show, $numberOfSeats);
        
        // Créer la réservation
        $booking = Booking::create($userId, $showId, $numberOfSeats);
        
        // Décrémenter les places disponibles
        $show->bookSeats($numberOfSeats);
        
        // Sauvegarder
        $this->bookingRepository->save($booking);
        $this->showRepository->save($show);
        
        return $booking;
    }
    
    public function getUserBookingsWithShows(UserId $userId): array
    {
        $bookings = $this->bookingRepository->findByUserId($userId);
        
        $result = [];
        foreach ($bookings as $booking) {
            $show = $this->showRepository->findById($booking->getShowId());
            
            $result[] = [
                'booking' => $booking,
                'show' => $show,
            ];
        }
        
        return $result;
    }
}