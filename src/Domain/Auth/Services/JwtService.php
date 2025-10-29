<?php

declare(strict_types=1);

namespace Spectreacle\Domain\Auth\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Spectreacle\Domain\User\Entities\User;
use Spectreacle\Shared\Exceptions\AuthenticationException;

class JwtService
{
    private const ALGORITHM = 'HS256';
    private const EXPIRATION_TIME = 900; // 15 minutes

    public function __construct(
        private string $secretKey
    ) {}

    public function generateToken(User $user): string
    {
        $payload = [
            'sub' => $user->getId(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
            'iat' => time(),
            'exp' => time() + self::EXPIRATION_TIME
        ];

        return JWT::encode($payload, $this->secretKey, self::ALGORITHM);
    }

    public function validateToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, self::ALGORITHM));
            return (array) $decoded;
        } catch (\Exception $e) {
            throw new AuthenticationException('Invalid token: ' . $e->getMessage());
        }
    }

    public function isTokenExpired(array $payload): bool
    {
        return isset($payload['exp']) && $payload['exp'] < time();
    }

    public function getUserIdFromToken(string $token): int
    {
        $payload = $this->validateToken($token);
        
        if ($this->isTokenExpired($payload)) {
            throw new AuthenticationException('Token has expired');
        }

        return (int) $payload['sub'];
    }
}