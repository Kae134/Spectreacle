<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;

final class CorsMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): ?Response
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        if ($request->getMethod() === 'OPTIONS') {
            return new Response('', 204);
        }
        
        return null;
    }
}
