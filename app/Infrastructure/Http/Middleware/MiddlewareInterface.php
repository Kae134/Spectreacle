<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;

interface MiddlewareInterface
{
    public function handle(Request $request): ?Response;
}
