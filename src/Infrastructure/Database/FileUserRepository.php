<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Database;

use Spectreacle\Domain\User\Entities\User;
use Spectreacle\Domain\User\Repositories\UserRepositoryInterface;

class FileUserRepository implements UserRepositoryInterface
{
    private array $users = [];
    private int $nextId = 1;
    private string $dataFile;

    public function __construct()
    {
        $this->dataFile = sys_get_temp_dir() . '/spectreacle_users.json';
        $this->loadUsers();
        
        if (empty($this->users)) {
            $this->initializeDefaultUsers();
        }
    }

    private function loadUsers(): void
    {
        if (file_exists($this->dataFile)) {
            $data = json_decode(file_get_contents($this->dataFile), true);
            if ($data && isset($data['users']) && isset($data['nextId'])) {
                $this->nextId = $data['nextId'];
                foreach ($data['users'] as $userData) {
                    $this->users[] = new User(
                        $userData['id'],
                        $userData['username'],
                        $userData['passwordHash'],
                        $userData['email'],
                        $userData['roles'],
                        $userData['totpSecret'] ?? null,
                        $userData['totpEnabled'] ?? false,
                        $userData['requiresTwoFactor'] ?? false
                    );
                }
            }
        }
    }

    private function saveUsers(): void
    {
        $data = [
            'nextId' => $this->nextId,
            'users' => []
        ];
        
        foreach ($this->users as $user) {
            $data['users'][] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'passwordHash' => $user->getPasswordHash(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'totpSecret' => $user->getTotpSecret(),
                'totpEnabled' => $user->isTotpEnabled(),
                'requiresTwoFactor' => $user->requiresTwoFactor()
            ];
        }
        
        file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function initializeDefaultUsers(): void
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
        
        $this->saveUsers();
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

    public function findByEmail(string $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getEmail() === $email) {
                return $user;
            }
        }
        return null;
    }

    public function save(User $user): User
    {
        $this->users[] = $user;
        $this->saveUsers();
        return $user;
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
        $this->saveUsers();
        return $user;
    }

    public function update(User $user): User
    {
        foreach ($this->users as $index => $existingUser) {
            if ($existingUser->getId() === $user->getId()) {
                $this->users[$index] = $user;
                $this->saveUsers();
                return $user;
            }
        }
        
        throw new \RuntimeException('User not found for update');
    }
}