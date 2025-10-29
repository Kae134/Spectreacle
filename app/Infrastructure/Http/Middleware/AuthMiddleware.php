<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\JsonResponse;
use App\Infrastructure\Security\JWTManager;

final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly JWTManager $jwtManager
    ) {
    }
    
    public function handle(Request $request): ?Response
    {
        $token = $this->extractToken($request);
        
        if ($token === null) {
            return JsonResponse::error('Authentication required', 401);
        }
        
        $payload = $this->jwtManager->verifyToken($token);
        
        if ($payload === null) {
            return JsonResponse::error('Invalid or expired token', 401);
        }
        
        // Stocker les infos utilisateur dans la session pour y accéder plus tard
        $_SESSION['user'] = $payload;
        
        return null;
    }
    
    private function extractToken(Request $request): ?string
    {
        // Essayer le cookie
        $token = $request->getCookie('jwt_token');
        if ($token !== null) {
            return $token;
        }
        
        // Essayer le header Authorization
        $authHeader = $request->getHeader('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }
        
        return null;
    }
}