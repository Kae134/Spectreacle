<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Http\Controllers;

use Spectreacle\Application\Auth\Services\AuthenticationService;
use Spectreacle\Domain\Show\Repositories\ShowRepositoryInterface;
use Spectreacle\Domain\Show\Repositories\ReservationRepositoryInterface;
use Spectreacle\Shared\Attributes\IsGranted;

class ReservationController
{
    public function __construct(
        private ShowRepositoryInterface $showRepository,
        private ReservationRepositoryInterface $reservationRepository,
        private AuthenticationService $authService
    ) {}

    #[IsGranted(requireAuth: true)]
    public function create(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['showId']) || !isset($input['numberOfSeats'])) {
            http_response_code(400);
            echo json_encode(['error' => 'showId et numberOfSeats sont requis']);
            return;
        }

        $showId = (int) $input['showId'];
        $numberOfSeats = (int) $input['numberOfSeats'];
        
        if ($numberOfSeats < 1 || $numberOfSeats > 8) {
            http_response_code(400);
            echo json_encode(['error' => 'Le nombre de places doit être entre 1 et 8']);
            return;
        }

        try {
            $show = $this->showRepository->findById($showId);
            if (!$show) {
                http_response_code(404);
                echo json_encode(['error' => 'Spectacle non trouvé']);
                return;
            }

            if (!$show->canReserveSeats($numberOfSeats)) {
                http_response_code(400);
                echo json_encode(['error' => 'Nombre de places insuffisant']);
                return;
            }

            $user = $this->getCurrentUser();
            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'Authentication required']);
                return;
            }
            
            $totalPrice = $show->getPrice() * $numberOfSeats;

            // Créer la réservation
            $reservation = $this->reservationRepository->create(
                $user->getId(),
                $showId,
                $numberOfSeats,
                $totalPrice
            );

            // Mettre à jour le nombre de places disponibles
            $show->reserveSeats($numberOfSeats);
            $this->showRepository->save($show);

            echo json_encode([
                'success' => true,
                'message' => 'Réservation confirmée !',
                'reservation' => [
                    'id' => $reservation->getId(),
                    'numberOfSeats' => $reservation->getNumberOfSeats(),
                    'totalPrice' => $reservation->getFormattedTotalPrice(),
                    'showTitle' => $show->getTitle()
                ]
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la réservation: ' . $e->getMessage()]);
        }
    }

    private function getCurrentUser(): ?\Spectreacle\Domain\User\Entities\User
    {
        $token = $_COOKIE['jwt_token'] ?? null;
        return $this->authService->getUserFromToken($token);
    }
}