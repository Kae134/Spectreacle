<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Http\Controllers;

use Spectreacle\Application\Auth\Services\AuthenticationService;
use Spectreacle\Domain\Show\Repositories\ShowRepositoryInterface;
use Spectreacle\Shared\Attributes\IsGranted;

class AdminController
{
    public function __construct(
        private ShowRepositoryInterface $showRepository,
        private AuthenticationService $authService
    ) {}

    #[IsGranted(role: 'admin')]
    public function index(): void
    {
        $user = $this->getCurrentUser();
        $shows = $this->showRepository->findAll();

        $this->renderHtml('admin/index', [
            'user' => $user,
            'shows' => $shows,
            'title' => 'Administration - Spectreacle'
        ]);
    }

    #[IsGranted(role: 'admin')]
    public function showCreateForm(): void
    {
        $user = $this->getCurrentUser();

        $this->renderHtml('admin/create-show', [
            'user' => $user,
            'title' => 'Ajouter un spectacle - Administration'
        ]);
    }

    #[IsGranted(role: 'admin')]
    public function createShow(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        $requiredFields = ['title', 'description', 'category', 'dateTime', 'venue', 'totalSeats', 'price'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty(trim($input[$field]))) {
                http_response_code(400);
                echo json_encode(['error' => "Le champ {$field} est requis"]);
                return;
            }
        }

        try {
            $dateTime = new \DateTime($input['dateTime']);
            
            if ($dateTime <= new \DateTime()) {
                http_response_code(400);
                echo json_encode(['error' => 'La date du spectacle doit être dans le futur']);
                return;
            }

            $show = $this->showRepository->create(
                trim($input['title']),
                trim($input['description']),
                trim($input['category']),
                $dateTime,
                trim($input['venue']),
                (int) $input['totalSeats'],
                (float) $input['price'],
                trim($input['imageUrl'] ?? '')
            );

            echo json_encode([
                'success' => true,
                'message' => 'Spectacle créé avec succès',
                'show' => [
                    'id' => $show->getId(),
                    'title' => $show->getTitle(),
                    'category' => $show->getCategory()
                ]
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => 'Erreur lors de la création: ' . $e->getMessage()]);
        }
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