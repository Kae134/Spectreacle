<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Http\Controllers;

use Spectreacle\Application\Auth\Services\AuthenticationService;
use Spectreacle\Domain\Show\Repositories\ShowRepositoryInterface;

class ShowController
{
    public function __construct(
        private ShowRepositoryInterface $showRepository,
        private AuthenticationService $authService
    ) {}

    public function index(): void
    {
        $user = $this->getCurrentUser();
        $shows = $this->showRepository->findAll();

        $this->renderHtml('shows/index', [
            'user' => $user,
            'shows' => $shows,
            'title' => 'Nos spectacles - Spectreacle'
        ]);
    }

    public function show(string $id): void
    {
        $user = $this->getCurrentUser();
        $show = $this->showRepository->findById((int) $id);

        if (!$show) {
            http_response_code(404);
            $this->renderHtml('errors/404', [
                'user' => $user,
                'title' => 'Spectacle non trouvé - Spectreacle'
            ]);
            return;
        }

        $this->renderHtml('shows/show', [
            'user' => $user,
            'show' => $show,
            'title' => $show->getTitle() . ' - Spectreacle'
        ]);
    }

    public function category(string $category): void
    {
        $user = $this->getCurrentUser();
        $shows = $this->showRepository->findByCategory($category);

        $categoryNames = [
            'théâtre' => 'Théâtre',
            'concert' => 'Concerts',
            'opéra' => 'Opéra'
        ];

        $categoryName = $categoryNames[$category] ?? ucfirst($category);

        $this->renderHtml('shows/category', [
            'user' => $user,
            'shows' => $shows,
            'category' => $category,
            'categoryName' => $categoryName,
            'title' => $categoryName . ' - Spectreacle'
        ]);
    }

    private function getCurrentUser(): ?\Spectreacle\Domain\User\Entities\User
    {
        $token = $_COOKIE['jwt_token'] ?? null;
        
        if ($token && $this->authService->validateToken($token)) {
            return $this->authService->getUserFromToken($token);
        }

        return null;
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