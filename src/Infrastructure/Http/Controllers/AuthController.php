<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Http\Controllers;

use Spectreacle\Application\Auth\Services\AuthenticationService;
use Spectreacle\Shared\Exceptions\AuthenticationException;
use Spectreacle\Shared\Exceptions\RegistrationException;

class AuthController
{
    public function __construct(
        private AuthenticationService $authService
    ) {}

    public function login(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['username']) || !isset($input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Username and password required']);
            return;
        }

        try {
            $token = $this->authService->authenticate($input['username'], $input['password']);
            
            // Créer le cookie JWT
            setcookie(
                'jwt_token',
                $token,
                [
                    'expires' => time() + 900, // 15 minutes
                    'path' => '/',
                    'httponly' => true,
                    'secure' => false, // Pour le développement
                    'samesite' => 'Lax'
                ]
            );

            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'token' => $token
            ]);
        } catch (AuthenticationException $e) {
            http_response_code(401);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function logout(): void
    {
        setcookie('jwt_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true
        ]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Logout successful']);
    }

    public function register(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        $requiredFields = ['username', 'email', 'password', 'confirmPassword'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty(trim($input[$field]))) {
                http_response_code(400);
                echo json_encode(['error' => "Le champ {$field} est requis"]);
                return;
            }
        }

        try {
            $token = $this->authService->register(
                trim($input['username']),
                trim($input['email']),
                $input['password'],
                $input['confirmPassword']
            );
            
            // Créer le cookie JWT
            setcookie(
                'jwt_token',
                $token,
                [
                    'expires' => time() + 900, // 15 minutes
                    'path' => '/',
                    'httponly' => true,
                    'secure' => false, // Pour le développement
                    'samesite' => 'Lax'
                ]
            );

            echo json_encode([
                'success' => true,
                'message' => 'Inscription réussie ! Vous êtes maintenant connecté.',
                'token' => $token
            ]);
        } catch (RegistrationException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}