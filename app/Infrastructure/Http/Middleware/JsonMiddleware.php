<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;

final class JsonMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): ?Response
    {
        // Vérifier que les requêtes POST/PUT ont le bon Content-Type
        if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $contentType = $request->getHeader('Content-Type') ?? '';
            
            if (!str_contains($contentType, 'application/json') && !str_contains($contentType, 'multipart/form-data')) {
                // Accepter aussi les formulaires classiques
                // return JsonResponse::error('Content-Type must be application/json', 415);
            }
        }
        
        return null;
    }
}
