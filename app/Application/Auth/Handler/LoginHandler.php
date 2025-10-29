<?php

declare(strict_types=1);

namespace App\Application\Auth\Handler;

use App\Application\Auth\Command\LoginCommand;
use App\Application\Auth\DTO\AuthResponse;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Infrastructure\Security\JWTManager;
use DomainException;

final class LoginHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly JWTManager $jwtManager
    ) {
    }
    
    public function handle(LoginCommand $command): AuthResponse
    {
        $user = $this->userRepository->findByUsername($command->username);
        
        if ($user === null || !$user->verifyPassword($command->password)) {
            throw new DomainException('Invalid credentials');
        }
        
        $token = $this->jwtManager->generateToken([
            'user_id' => $user->getId()->toString(),
            'username' => $user->getUsername(),
            'role' => $user->getRole()->toString(),
        ]);
        
        return new AuthResponse(
            token: $token,
            userId: $user->getId()->toString(),
            username: $user->getUsername(),
            role: $user->getRole()->toString()
        );
    }
}