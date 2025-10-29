<?php

declare(strict_types=1);

namespace App\Domain\User\Entity;

use App\Domain\User\ValueObject\UserId;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserRole;
use App\Domain\User\ValueObject\HashedPassword;

final class User
{
    private function __construct(
        private UserId $id,
        private string $username,
        private Email $email,
        private HashedPassword $password,
        private UserRole $role
    ) {
    }
    
    public static function create(
        string $username,
        Email $email,
        HashedPassword $password,
        ?UserRole $role = null
    ): self {
        return new self(
            UserId::generate(),
            $username,
            $email,
            $password,
            $role ?? UserRole::user()
        );
    }
    
    public static function reconstitute(
        UserId $id,
        string $username,
        Email $email,
        HashedPassword $password,
        UserRole $role
    ): self {
        return new self($id, $username, $email, $password, $role);
    }
    
    // Getters
    public function getId(): UserId
    {
        return $this->id;
    }
    
    public function getUsername(): string
    {
        return $this->username;
    }
    
    public function getEmail(): Email
    {
        return $this->email;
    }
    
    public function getPassword(): HashedPassword
    {
        return $this->password;
    }
    
    public function getRole(): UserRole
    {
        return $this->role;
    }
    
    // Business logic
    public function isAdmin(): bool
    {
        return $this->role->isAdmin();
    }
    
    public function verifyPassword(string $plainPassword): bool
    {
        return $this->password->verify($plainPassword);
    }
    
    public function changePassword(HashedPassword $newPassword): void
    {
        $this->password = $newPassword;
    }
    
    public function changeEmail(Email $newEmail): void
    {
        $this->email = $newEmail;
    }
    
    public function promoteToAdmin(): void
    {
        $this->role = UserRole::admin();
    }
    
    public function demoteToUser(): void
    {
        $this->role = UserRole::user();
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'username' => $this->username,
            'email' => $this->email->toString(),
            'role' => $this->role->toString(),
        ];
    }
}