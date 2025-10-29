<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Http\Controllers;

use Spectreacle\Application\Auth\Services\AuthenticationService;
use Spectreacle\Shared\Attributes\IsGranted;

class ProtectedController
{
    public function __construct(
        private AuthenticationService $authService
    ) {}

    #[IsGranted(requireAuth: true)]
    public function dashboard(): void
    {
        $token = $_COOKIE['jwt_token'] ?? null;
        $user = $this->authService->getUserFromToken($token);

        $this->renderHtml('dashboard', [
            'user' => $user,
            'title' => 'Tableau de bord - Spectreacle'
        ]);
    }

    #[IsGranted(role: 'admin')]
    public function adminPanel(): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'message' => 'Welcome to admin panel',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
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