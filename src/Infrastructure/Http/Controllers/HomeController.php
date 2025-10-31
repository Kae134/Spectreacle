<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Http\Controllers;

use Spectreacle\Application\Auth\Services\AuthenticationService;

class HomeController
{
    public function __construct(
        private AuthenticationService $authService
    ) {}

    public function index(): void
    {
        $user = null;
        $token = $_COOKIE['jwt_token'] ?? null;
        
        if ($token && $this->authService->validateToken($token)) {
            $user = $this->authService->getUserFromToken($token);
        }

        $this->renderHtml('home', [
            'user' => $user,
            'title' => 'Accueil - Spectreacle'
        ]);
    }

    public function showLoginForm(): void
    {
        $this->renderHtml('login', [
            'title' => 'Connexion - Spectreacle'
        ]);
    }

    public function showRegisterForm(): void
    {
        $this->renderHtml('register', [
            'title' => 'Inscription - Spectreacle'
        ]);
    }

    public function showTotpSetup(): void
    {
        // Vérifier que l'utilisateur est connecté
        $token = $_COOKIE['jwt_token'] ?? null;
        if (!$token || !$this->authService->validateToken($token)) {
            header('Location: /login');
            return;
        }

        $user = $this->authService->getUserFromToken($token);
        if (!$user) {
            header('Location: /login');
            return;
        }

        $this->renderHtml('totp-setup', [
            'title' => 'Configuration TOTP - Spectreacle',
            'user' => $user
        ]);
    }

    public function health(): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'OK',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
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