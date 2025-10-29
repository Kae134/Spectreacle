<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Http\Controllers;

use Spectreacle\Application\Auth\Services\AuthenticationService;
use Spectreacle\Domain\Show\Repositories\ShowRepositoryInterface;
use Spectreacle\Domain\Show\Repositories\ReservationRepositoryInterface;
use Spectreacle\Shared\Attributes\IsGranted;

class ProfileController
{
    public function __construct(
        private ShowRepositoryInterface $showRepository,
        private ReservationRepositoryInterface $reservationRepository,
        private AuthenticationService $authService
    ) {}

    #[IsGranted(requireAuth: true)]
    public function index(): void
    {
        $user = $this->getCurrentUser();
        $reservations = $this->reservationRepository->findByUserId($user->getId());
        
        // Enrichir les réservations avec les informations des spectacles
        $enrichedReservations = [];
        foreach ($reservations as $reservation) {
            $show = $this->showRepository->findById($reservation->getShowId());
            $enrichedReservations[] = [
                'reservation' => $reservation,
                'show' => $show
            ];
        }

        $this->renderHtml('profile/index', [
            'user' => $user,
            'reservations' => $enrichedReservations,
            'title' => 'Mon profil - Spectreacle'
        ]);
    }

    private function getCurrentUser(): ?\Spectreacle\Domain\User\Entities\User
    {
        $token = $_COOKIE['jwt_token'] ?? null;
        return $this->authService->getUserFromToken($token);
    }

    private function renderHtml(string $template, array $data = []): void
    {
        extract($data);
        
        ob_start();
        include __DIR__ . "/../../../../public/templates/{$template}.php";
        $content = ob_get_clean();
        
        echo $content;
    }
}