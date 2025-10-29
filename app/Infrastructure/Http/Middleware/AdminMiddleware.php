<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\JsonResponse;

final class AdminMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly AuthMiddleware $authMiddleware
    ) {
    }
    
    public function handle(Request $request): ?Response
    {
        // D'abord vérifier l'authentification
        $authResult = $this->authMiddleware->handle($request);
        if ($authResult !== null) {
            return $authResult;
        }
        
        // Vérifier le rôle admin
        $user = $_SESSION['user'] ?? null;
        
        if ($user === null || ($user['role'] ?? '') !== 'ROLE_ADMIN') {
            return JsonResponse::error('Admin access required', 403);
        }
        
        return null;
    }
}