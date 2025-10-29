<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Database;

use Spectreacle\Domain\User\Entities\User;
use Spectreacle\Domain\User\Repositories\UserRepositoryInterface;

class InMemoryUserRepository implements UserRepositoryInterface
{
    private array $users = [];
    private int $nextId = 1;

    public function __construct()
    {
        // Ajouter quelques utilisateurs de test
        $this->users[] = new User(
            $this->nextId++,
            'admin',
            password_hash('admin123', PASSWORD_DEFAULT),
            'admin@spectreacle.com',
            ['admin', 'user']
        );
        
        $this->users[] = new User(
            $this->nextId++,
            'user1',
            password_hash('password123', PASSWORD_DEFAULT),
            'user1@spectreacle.com',
            ['user']
        );
    }

    public function findById(int $id): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getId() === $id) {
                return $user;
            }
        }
        return null;
    }

    public function findByUsername(string $username): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getUsername() === $username) {
                return $user;
            }
        }
        return null;
    }

    public function save(User $user): User
    {
        $this->users[] = $user;
        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getEmail() === $email) {
                return $user;
            }
        }
        return null;
    }

    public function create(string $username, string $email, string $password): User
    {
        $user = new User(
            $this->nextId++,
            $username,
            password_hash($password, PASSWORD_DEFAULT),
            $email,
            ['user'] // Les nouveaux utilisateurs sont des utilisateurs normaux
        );
        
        $this->users[] = $user;
        return $user;
    }
}