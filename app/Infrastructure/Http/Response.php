<?php 

declare(strict_types=1);

namespace App\Infrastructure\Http;

final class Response
{
    private array $headers = [];
    
    public function __construct(
        private string $content = '',
        private int $statusCode = 200
    ) {
    }
    
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }
    
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }
    
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }
    
    public function setCookie(
        string $name,
        string $value,
        int $expires = 0,
        string $path = '/',
        bool $httpOnly = true,
        bool $secure = false
    ): self {
        setcookie($name, $value, [
            'expires' => $expires,
            'path' => $path,
            'httponly' => $httpOnly,
            'secure' => $secure,
            'samesite' => 'Lax',
        ]);
        return $this;
    }
    
    public function redirect(string $url, int $statusCode = 302): self
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Location', $url);
        return $this;
    }
    
    public function send(): void
    {
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
        
        echo $this->content;
    }
}