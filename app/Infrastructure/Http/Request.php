<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

final class Request
{
    private function __construct(
        private readonly string $method,
        private readonly string $uri,
        private readonly array $query,
        private readonly array $post,
        private readonly array $server,
        private readonly array $cookies,
        private readonly ?string $body
    ) {
    }
    
    public static function createFromGlobals(): self
    {
        return new self(
            method: $_SERVER['REQUEST_METHOD'] ?? 'GET',
            uri: parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH),
            query: $_GET,
            post: $_POST,
            server: $_SERVER,
            cookies: $_COOKIE,
            body: file_get_contents('php://input') ?: null
        );
    }
    
    public function getMethod(): string
    {
        return $this->method;
    }
    
    public function getUri(): string
    {
        return rtrim($this->uri, '/') ?: '/';
    }
    
    public function getQuery(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }
    
    public function getPost(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }
    
    public function getCookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }
    
    public function getHeader(string $name): ?string
    {
        $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server[$headerKey] ?? null;
    }
    
    public function getBody(): ?string
    {
        return $this->body;
    }
    
    public function getJsonBody(): ?array
    {
        if ($this->body === null) {
            return null;
        }
        
        $decoded = json_decode($this->body, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }
    
    public function isPost(): bool
    {
        return $this->method === 'POST';
    }
    
    public function isGet(): bool
    {
        return $this->method === 'GET';
    }
    
    public function isJson(): bool
    {
        $contentType = $this->getHeader('Content-Type') ?? '';
        return str_contains($contentType, 'application/json');
    }
}