<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Attribute;

use Attribute;
use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\JsonResponse;
use App\Infrastructure\Security\JWTManager;

#[Attribute(Attribute::TARGET_METHOD)]
final class IsGranted
{
    public function __construct(
        private readonly string $role = 'ROLE_USER'
    ) {
    }
    
    public function check(Request $request): ?Response
    {
        // Extraire le token
        $token = $this->extractToken($request);
        
        if ($token === null) {
            // Pour les pages web, rediriger vers login
            if (!$this->isApiRequest($request)) {
                return (new Response())->redirect('/login');
            }
            return JsonResponse::error('Authentication required', 401);
        }
        
        $jwtManager = new JWTManager();
        $payload = $jwtManager->verifyToken($token);
        
        if ($payload === null) {
            if (!$this->isApiRequest($request)) {
                // Supprimer le cookie invalide
                setcookie('jwt_token', '', time() - 3600, '/');
                return (new Response())->redirect('/login');
            }
            return JsonResponse::error('Invalid or expired token', 401);
        }
        
        // Stocker en session
        $_SESSION['user'] = $payload;
        
        // Vérifier le rôle si nécessaire
        if ($this->role === 'ROLE_ADMIN') {
            if (($payload['role'] ?? '') !== 'ROLE_ADMIN') {
                if (!$this->isApiRequest($request)) {
                    return (new Response())->setStatusCode(403)->setContent('Access forbidden');
                }
                return JsonResponse::error('Admin access required', 403);
            }
        }
        
        return null;
    }
    
    private function extractToken(Request $request): ?string
    {
        // Cookie
        $token = $request->getCookie('jwt_token');
        if ($token !== null) {
            return $token;
        }
        
        // Header
        $authHeader = $request->getHeader('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }
        
        return null;
    }
    
    private function isApiRequest(Request $request): bool
    {
        return str_starts_with($request->getUri(), '/api/');
    }
    
    public function getRole(): string
    {
        return $this->role;
    }
}