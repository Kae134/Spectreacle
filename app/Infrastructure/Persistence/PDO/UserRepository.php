<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\PDO;

use PDO;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\UserId;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserRole;
use App\Domain\User\ValueObject\HashedPassword;
use App\Infrastructure\Database\PDOConnection;

final class UserRepository implements UserRepositoryInterface
{
    private PDO $pdo;
    
    public function __construct()
    {
        $this->pdo = PDOConnection::getInstance();
    }
    
    public function save(User $user): void
    {
        $data = [
            'id' => $user->getId()->toString(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail()->toString(),
            'password' => $user->getPassword()->toString(),
            'role' => $user->getRole()->toString(),
        ];
        
        $sql = "INSERT INTO users (id, username, email, password, role, created_at, updated_at) 
                VALUES (:id, :username, :email, :password, :role, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                    username = VALUES(username),
                    email = VALUES(email),
                    password = VALUES(password),
                    role = VALUES(role),
                    updated_at = NOW()";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }
    
    public function findById(UserId $id): ?User
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id->toString()]);
        
        $data = $stmt->fetch();
        
        return $data ? $this->hydrate($data) : null;
    }
    
    public function findByUsername(string $username): ?User
    {
        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['username' => $username]);
        
        $data = $stmt->fetch();
        
        return $data ? $this->hydrate($data) : null;
    }
    
    public function findByEmail(Email $email): ?User
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email->toString()]);
        
        $data = $stmt->fetch();
        
        return $data ? $this->hydrate($data) : null;
    }
    
    public function findAll(): array
    {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $stmt = $this->pdo->query($sql);
        
        $users = [];
        while ($data = $stmt->fetch()) {
            $users[] = $this->hydrate($data);
        }
        
        return $users;
    }
    
    public function exists(UserId $id): bool
    {
        return $this->findById($id) !== null;
    }
    
    public function delete(UserId $id): void
    {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id->toString()]);
    }
    
    private function hydrate(array $data): User
    {
        return User::reconstitute(
            id: UserId::fromString($data['id']),
            username: $data['username'],
            email: Email::fromString($data['email']),
            password: HashedPassword::fromHash($data['password']),
            role: UserRole::fromString($data['role'])
        );
    }
}