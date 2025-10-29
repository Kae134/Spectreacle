<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Infrastructure\Config\AppConfig;

final class JWTManager
{
    private string $secretKey;
    private int $expiration;
    
    public function __construct()
    {
        $config = AppConfig::getInstance();
        $this->secretKey = $config->get('jwt.secret_key');
        $this->expiration = $config->get('jwt.expiration');
    }
    
    public function generateToken(array $payload): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        $payload['iat'] = time();
        $payload['exp'] = time() + $this->expiration;
        
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));
        
        $signature = hash_hmac(
            'sha256',
            $base64UrlHeader . "." . $base64UrlPayload,
            $this->secretKey,
            true
        );
        $base64UrlSignature = $this->base64UrlEncode($signature);
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    public function verifyToken(string $token): ?array
    {
        $tokenParts = explode('.', $token);
        
        if (count($tokenParts) !== 3) {
            return null;
        }
        
        [$base64UrlHeader, $base64UrlPayload, $base64UrlSignature] = $tokenParts;
        
        $signature = $this->base64UrlEncode(
            hash_hmac(
                'sha256',
                $base64UrlHeader . "." . $base64UrlPayload,
                $this->secretKey,
                true
            )
        );
        
        if ($signature !== $base64UrlSignature) {
            return null;
        }
        
        $payload = json_decode($this->base64UrlDecode($base64UrlPayload), true);
        
        if (!isset($payload['exp']) || $payload['exp'] < time()) {
            return null;
        }
        
        return $payload;
    }
    
    private function base64UrlEncode(string $text): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
    }
    
    private function base64UrlDecode(string $text): string
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $text));
    }
}
