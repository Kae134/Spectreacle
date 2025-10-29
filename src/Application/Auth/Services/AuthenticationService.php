<?php

declare(strict_types=1);

namespace Spectreacle\Application\Auth\Services;

use Spectreacle\Domain\Auth\Services\JwtService;
use Spectreacle\Domain\User\Repositories\UserRepositoryInterface;
use Spectreacle\Shared\Exceptions\AuthenticationException;
use Spectreacle\Shared\Exceptions\RegistrationException;

class AuthenticationService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private JwtService $jwtService
    ) {}

    public function authenticate(string $username, string $password): string
    {
        $user = $this->userRepository->findByUsername($username);
        
        if (!$user || !$user->verifyPassword($password)) {
            throw new AuthenticationException('Invalid credentials');
        }

        return $this->jwtService->generateToken($user);
    }

    public function getUserFromToken(string $token): ?\Spectreacle\Domain\User\Entities\User
    {
        try {
            $userId = $this->jwtService->getUserIdFromToken($token);
            return $this->userRepository->findById($userId);
        } catch (AuthenticationException $e) {
            return null;
        }
    }

    public function validateToken(string $token): bool
    {
        try {
            $payload = $this->jwtService->validateToken($token);
            return !$this->jwtService->isTokenExpired($payload);
        } catch (AuthenticationException $e) {
            return false;
        }
    }

    public function register(string $username, string $email, string $password, string $confirmPassword): string
    {
        // Validation des données
        $this->validateRegistrationData($username, $email, $password, $confirmPassword);

        // Vérifier que l'utilisateur n'existe pas déjà
        if ($this->userRepository->findByUsername($username)) {
            throw new RegistrationException('Ce nom d\'utilisateur est déjà pris');
        }

        if ($this->userRepository->findByEmail($email)) {
            throw new RegistrationException('Cette adresse email est déjà utilisée');
        }

        // Créer l'utilisateur
        $user = $this->userRepository->create($username, $email, $password);

        // Générer un token JWT pour connecter automatiquement l'utilisateur
        return $this->jwtService->generateToken($user);
    }

    private function validateRegistrationData(string $username, string $email, string $password, string $confirmPassword): void
    {
        if (strlen($username) < 3) {
            throw new RegistrationException('Le nom d\'utilisateur doit contenir au moins 3 caractères');
        }

        if (strlen($username) > 20) {
            throw new RegistrationException('Le nom d\'utilisateur ne peut pas dépasser 20 caractères');
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            throw new RegistrationException('Le nom d\'utilisateur ne peut contenir que des lettres, chiffres et tirets bas');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RegistrationException('L\'adresse email n\'est pas valide');
        }

        if (strlen($password) < 6) {
            throw new RegistrationException('Le mot de passe doit contenir au moins 6 caractères');
        }

        if ($password !== $confirmPassword) {
            throw new RegistrationException('Les mots de passe ne correspondent pas');
        }
    }
}