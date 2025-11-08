<?php

declare(strict_types=1);

namespace Spectreacle\Application\Auth\Services;

use Spectreacle\Domain\Auth\Services\JwtService;
use Spectreacle\Domain\Auth\Services\TotpService;
use Spectreacle\Domain\User\Repositories\UserRepositoryInterface;
use Spectreacle\Shared\Exceptions\AuthenticationException;
use Spectreacle\Shared\Exceptions\RegistrationException;

class AuthenticationService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private JwtService $jwtService,
        private TotpService $totpService
    ) {}

    public function beginPasswordLogin(string $username, string $password): array
    {
        $user = $this->userRepository->findByUsername($username);
        if (!$user || !$user->verifyPassword($password)) {
            throw new AuthenticationException('Invalid credentials');
        }

        // Si 2FA désactivé → connecte direct
        if (!$user->requiresTwoFactor()) {
            return [
                'status' => 'ok',
                'token'  => $this->jwtService->generateToken($user),
            ];
        }

        // 2FA actif → route selon méthode
        $method = method_exists($user, 'getTwoFactorMethod') ? $user->getTwoFactorMethod() : 'totp';

        if ($method === 'totp') {
            return ['status' => 'requires_totp'];
        }

        if ($method === 'sms') {
            return [
                'status'   => 'requires_otp',
                'channel'  => 'sms',
                'user_id'  => $user->getId(),
                'username' => $user->getUsername(),
                'phone'    => method_exists($user, 'getPhoneE164') ? $user->getPhoneE164() : null,
                'email'    => $user->getEmail(),
            ];
        }

        if ($method === 'email') {
            return [
                'status'   => 'requires_otp',
                'channel'  => 'email',
                'user_id'  => $user->getId(),
                'username' => $user->getUsername(),
                'phone'    => method_exists($user, 'getPhoneE164') ? $user->getPhoneE164() : null,
                'email'    => $user->getEmail(),
            ];
        }

        throw new AuthenticationException('Unsupported 2FA method');
    }

    public function finalizeOtpLogin(int $userId): string
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new AuthenticationException('User not found');
        }
        // On pourrait ici marquer un "last_login_2fa_at"
        return $this->jwtService->generateToken($user);
    }

    public function authenticate(string $username, string $password, ?string $totpCode = null): string
    {
        $user = $this->userRepository->findByUsername($username);
        
        if (!$user || !$user->verifyPassword($password)) {
            throw new AuthenticationException('Invalid credentials');
        }

        // Si l'utilisateur a activé TOTP, vérifier le code
        if ($user->requiresTwoFactor()) {
            if (!$totpCode) {
                throw new AuthenticationException('TOTP_REQUIRED');
            }
            
            if (!$this->verifyTotpCode($user, $totpCode)) {
                throw new AuthenticationException('Invalid TOTP code');
            }
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

    public function setupTotp(int $userId): array
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new AuthenticationException('User not found');
        }

        $secret = $this->totpService->generateSecret();
        $user->setTotpSecret($secret);
        $this->userRepository->update($user);

        $qrCodeUrl = $this->totpService->generateQrCodeUrl($user);

        return [
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl
        ];
    }

    public function enableTotp(int $userId, string $totpCode): bool
    {
        $user = $this->userRepository->findById($userId);
        if (!$user || !$user->getTotpSecret()) {
            throw new AuthenticationException('TOTP not configured');
        }

        if (!$this->verifyTotpCode($user, $totpCode)) {
            throw new AuthenticationException('Invalid TOTP code');
        }

        $user->enableTotp();
        $this->userRepository->update($user);

        return true;
    }

    public function disableTotp(int $userId, string $totpCode): bool
    {
        $user = $this->userRepository->findById($userId);
        if (!$user || !$user->isTotpEnabled()) {
            throw new AuthenticationException('TOTP not enabled');
        }

        if (!$this->verifyTotpCode($user, $totpCode)) {
            throw new AuthenticationException('Invalid TOTP code');
        }

        $user->disableTotp();
        $this->userRepository->update($user);

        return true;
    }

    private function verifyTotpCode(\Spectreacle\Domain\User\Entities\User $user, string $code): bool
    {
        $secret = $user->getTotpSecret();
        if (!$secret) {
            return false;
        }

        // Nettoyer le code (supprimer espaces et caractères non-numériques)
        $cleanCode = preg_replace('/[^0-9]/', '', trim($code));

        return $this->totpService->verifyCode($secret, $cleanCode);
    }


    public function saveUserPhone(int $userId, string $phone): void
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new AuthenticationException("User not found");
        }

        // Ajouter dans User ton setter
        $user->setPhoneE164($phone);

        $this->userRepository->update($user);
    }

    public function activateSms2fa(int $userId): void
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) throw new AuthenticationException("User not found");

        $user->setTwoFactorMethod("sms");
        $user->enableTotp(); // active 2FA de manière générique

        $this->userRepository->update($user);
    }

    public function activateEmail2fa(int $userId): void
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) throw new AuthenticationException("User not found");

        $user->setTwoFactorMethod("email");
        $user->enableTotp();

        $this->userRepository->update($user);
    }
}