<?php

declare(strict_types=1);

namespace App\Domain\User\Repository;

use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\UserId;
use App\Domain\User\ValueObject\Email;

interface UserRepositoryInterface
{
    public function save(User $user): void;
    
    public function findById(UserId $id): ?User;
    
    public function findByUsername(string $username): ?User;
    
    public function findByEmail(Email $email): ?User;
    
    public function findAll(): array;
    
    public function exists(UserId $id): bool;
    
    public function delete(UserId $id): void;
}
