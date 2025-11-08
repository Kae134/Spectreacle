<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Database;

use Spectreacle\Domain\User\Entities\User;
use Spectreacle\Domain\User\Repositories\UserRepositoryInterface;

class InMemoryUserRepository implements UserRepositoryInterface
{
    /** @var array<int,User> */
    private array $users = [];

    private int $autoIncrement = 1;

    public function __construct()
    {
        // Tu peux prévoir quelques utilisateurs ici si tu veux
        // Exemple :
        // $this->create("admin", "admin@example.com", "admin123");
    }

    public function findById(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function findByUsername(string $username): ?User
    {
        foreach ($this->users as $user) {
            if (strtolower($user->getUsername()) === strtolower($username)) {
                return $user;
            }
        }
        return null;
    }

    public function findByEmail(string $email): ?User
    {
        foreach ($this->users as $user) {
            if (strtolower($user->getEmail()) === strtolower($email)) {
                return $user;
            }
        }
        return null;
    }

    public function create(string $username, string $email, string $password): User
    {
        $id = $this->autoIncrement++;

        $user = new User(
            id: $id,
            username: $username,
            passwordHash: password_hash($password, PASSWORD_ARGON2ID),
            email: $email,
            roles: ['user'],
            totpSecret: null,
            totpEnabled: false,
            requiresTwoFactor: false
        );

        $this->users[$id] = $user;

        return $user;
    }

    public function save(User $user): User
    {
        // sauvegarde naive
        $this->users[$user->getId()] = $user;
        return $user;
    }

    public function update(User $user): User
    {
        if (!isset($this->users[$user->getId()])) {
            throw new \RuntimeException("Cannot update user, not found.");
        }

        $this->users[$user->getId()] = $user;
        return $user;
    }
}
