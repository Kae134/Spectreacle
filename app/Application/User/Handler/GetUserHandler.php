<?php

declare(strict_types=1);

namespace App\Application\User\Handler;

use App\Application\User\Query\GetUserQuery;
use App\Application\User\DTO\UserResponse;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\UserId;
use DomainException;

final class GetUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {
    }
    
    public function handle(GetUserQuery $query): UserResponse
    {
        $userId = UserId::fromString($query->userId);
        $user = $this->userRepository->findById($userId);
        
        if ($user === null) {
            throw new DomainException('User not found');
        }
        
        return UserResponse::fromUser($user);
    }
}