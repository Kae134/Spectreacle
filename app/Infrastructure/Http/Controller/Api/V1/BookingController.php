<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Api\V1;

use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\JsonResponse;
use App\Infrastructure\Http\Attribute\IsGranted;
use App\Application\Booking\Command\CreateBookingCommand;
use App\Application\Booking\Handler\CreateBookingHandler;
use App\Domain\Booking\Service\BookingService;
use App\Domain\User\ValueObject\UserId;

final class BookingController
{
    public function __construct(
        private readonly BookingService $bookingService
    ) {
    }
    
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): Response
    {
        try {
            $user = $_SESSION['user'] ?? null;
            
            if ($user === null) {
                return JsonResponse::error('User not authenticated', 401);
            }
            
            $data = $request->isJson() 
                ? $request->getJsonBody()
                : [
                    'show_id' => $request->getPost('show_id'),
                    'number_of_seats' => $request->getPost('number_of_seats', 1),
                ];
            
            $command = new CreateBookingCommand(
                userId: $user['user_id'],
                showId: (int) ($data['show_id'] ?? 0),
                numberOfSeats: (int) ($data['number_of_seats'] ?? 1)
            );
            
            $handler = new CreateBookingHandler($this->bookingService);
            $response = $handler->handle($command);
            
            return JsonResponse::success($response->toArray(), 201);
            
        } catch (\DomainException $e) {
            return JsonResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return JsonResponse::error('An error occurred while creating booking', 500);
        }
    }
    
    #[IsGranted('ROLE_USER')]
    public function myBookings(Request $request): Response
    {
        try {
            $user = $_SESSION['user'] ?? null;
            
            if ($user === null) {
                return JsonResponse::error('User not authenticated', 401);
            }
            
            $userId = UserId::fromString($user['user_id']);
            $bookingsWithShows = $this->bookingService->getUserBookingsWithShows($userId);
            
            $data = array_map(function($item) {
                return [
                    'booking' => $item['booking']->toArray(),
                    'show' => $item['show']?->toArray(),
                ];
            }, $bookingsWithShows);
            
            return JsonResponse::success($data);
            
        } catch (\Exception $e) {
            return JsonResponse::error('An error occurred', 500);
        }
    }
}
