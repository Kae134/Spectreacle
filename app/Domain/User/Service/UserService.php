<?php

declare(strict_types=1);

namespace App\Domain\User\Service;

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use DomainException;

final class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {
    }
    
    public function isUsernameTaken(string $username): bool
    {
        return $this->userRepository->findByUsername($username) !== null;
    }
    
    public function isEmailTaken(Email $email): bool
    {
        return $this->userRepository->findByEmail($email) !== null;
    }
    
    public function ensureUsernameIsAvailable(string $username): void
    {
        if ($this->isUsernameTaken($username)) {
            throw new DomainException("Username '{$username}' is already taken");
        }
    }
    
    public function ensureEmailIsAvailable(Email $email): void
    {
        if ($this->isEmailTaken($email)) {
            throw new DomainException("Email '{$email->toString()}' is already registered");
        }
    }
}