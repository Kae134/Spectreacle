<?php

declare(strict_types=1);

namespace Spectreacle\Domain\User\Entities;

class User
{
    public function __construct(
        private int $id,
        private string $username,
        private string $passwordHash,
        private string $email,
        private array $roles = ['user'],
        private ?string $totpSecret = null,
        private bool $totpEnabled = false,
        private bool $requiresTwoFactor = false,

        private ?string $phoneE164 = null,
        private string $twoFactorMethod = 'totp'
    ) {}

    public function getPhoneE164(): ?string { return $this->phoneE164; }
    public function setPhoneE164(?string $p): void { $this->phoneE164 = $p; }

    public function getTwoFactorMethod(): string { return $this->twoFactorMethod; }
    public function setTwoFactorMethod(string $m): void { $this->twoFactorMethod = $m; }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    public function getTotpSecret(): ?string
    {
        return $this->totpSecret;
    }

    public function setTotpSecret(string $secret): void
    {
        $this->totpSecret = $secret;
    }

    public function isTotpEnabled(): bool
    {
        return $this->totpEnabled;
    }

    public function enableTotp(): void
    {
        $this->totpEnabled = true;
        $this->requiresTwoFactor = true;
    }

    public function disableTotp(): void
    {
        $this->totpEnabled = false;
        $this->totpSecret = null;
        $this->requiresTwoFactor = false;
    }

    public function requiresTwoFactor(): bool
    {
        return $this->requiresTwoFactor;
    }
}