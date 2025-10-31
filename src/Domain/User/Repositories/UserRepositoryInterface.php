<?php

declare(strict_types=1);

namespace Spectreacle\Domain\User\Repositories;

use Spectreacle\Domain\User\Entities\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByUsername(string $username): ?User;
    public function findByEmail(string $email): ?User;
    public function save(User $user): User;
    public function update(User $user): User;
    public function create(string $username, string $email, string $password): User;
}