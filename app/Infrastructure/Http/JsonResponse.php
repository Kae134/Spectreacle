<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

final class JsonResponse extends Response
{
    public function __construct(
        mixed $data,
        int $statusCode = 200
    ) {
        $content = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        parent::__construct($content, $statusCode);
        $this->setHeader('Content-Type', 'application/json');
    }
    
    public static function success(mixed $data, int $statusCode = 200): self
    {
        return new self(['success' => true, 'data' => $data], $statusCode);
    }
    
    public static function error(string $message, int $statusCode = 400): self
    {
        return new self(['success' => false, 'error' => $message], $statusCode);
    }
}
