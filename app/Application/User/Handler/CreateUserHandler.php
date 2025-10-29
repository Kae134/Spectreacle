<?php

declare(strict_types=1);

namespace App\Application\User\Handler;

use App\Application\User\Command\CreateUserCommand;
use App\Application\User\DTO\UserResponse;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\UserService;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use App\Domain\User\ValueObject\UserRole;

final class CreateUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserService $userService
    ) {
    }
    
    public function handle(CreateUserCommand $command): UserResponse
    {
        $email = Email::fromString($command->email);
        
        // Vérifier unicité
        $this->userService->ensureUsernameIsAvailable($command->username);
        $this->userService->ensureEmailIsAvailable($email);
        
        // Créer l'utilisateur
        $user = User::create(
            username: $command->username,
            email: $email,
            password: HashedPassword::fromPlainPassword($command->password),
            role: $command->role ? UserRole::fromString($command->role) : null
        );
        
        $this->userRepository->save($user);
        
        return UserResponse::fromUser($user);
    }
}