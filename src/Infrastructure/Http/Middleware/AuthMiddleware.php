<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Http\Middleware;

use Spectreacle\Application\Auth\Services\AuthenticationService;

class AuthMiddleware
{
    public function __construct(
        private AuthenticationService $authService
    ) {}

    public function handle(): ?array
    {
        $token = $_COOKIE['jwt_token'] ?? null;
        
        if (!$token) {
            return $this->unauthorizedResponse('Authentication required');
        }

        if (!$this->authService->validateToken($token)) {
            return $this->unauthorizedResponse('Invalid or expired token');
        }

        $user = $this->authService->getUserFromToken($token);
        if (!$user) {
            return $this->unauthorizedResponse('User not found');
        }

        // Ajouter l'utilisateur au contexte pour qu'il soit accessible dans les contrôleurs
        $_SESSION['current_user'] = $user;

        return null; // Pas d'erreur, continuer
    }

    private function unauthorizedResponse(string $message): array
    {
        http_response_code(401);
        header('Content-Type: application/json');
        return ['error' => $message];
    }
}