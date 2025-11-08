<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Http\Controllers;

use Spectreacle\Application\Auth\Services\AuthenticationService;
use Spectreacle\Domain\Auth\Services\TotpService;
use Spectreacle\Shared\Exceptions\AuthenticationException;
use Spectreacle\Shared\Exceptions\RegistrationException;
use Spectreacle\Application\Auth\Services\OtpService;

class AuthController
{
    public function __construct(
        private AuthenticationService $authService,
        private TotpService $totpService,
        private OtpService $otpService
    ) {}

    public function login(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['username']) || !isset($input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Username and password required']);
            return;
        }

        try {
            $totpCode = $input['totp_code'] ?? null;
            $token = $this->authService->authenticate($input['username'], $input['password'], $totpCode);
            
            // Créer le cookie JWT
            setcookie(
                'jwt_token',
                $token,
                [
                    'expires' => time() + 900, // 15 minutes
                    'path' => '/',
                    'httponly' => true,
                    'secure' => false, // Pour le développement
                    'samesite' => 'Lax'
                ]
            );

            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'token' => $token
            ]);
        } catch (AuthenticationException $e) {
            http_response_code(401);
            $response = ['error' => $e->getMessage()];
            
            // Si TOTP est requis, l'indiquer dans la réponse
            if ($e->getMessage() === 'TOTP_REQUIRED') {
                $response['requires_totp'] = true;
            }
            
            echo json_encode($response);
        }
    }

    public function logout(): void
    {
        setcookie('jwt_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true
        ]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Logout successful']);
    }

    public function register(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        $requiredFields = ['username', 'email', 'password', 'confirmPassword'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty(trim($input[$field]))) {
                http_response_code(400);
                echo json_encode(['error' => "Le champ {$field} est requis"]);
                return;
            }
        }

        try {
            $token = $this->authService->register(
                trim($input['username']),
                trim($input['email']),
                $input['password'],
                $input['confirmPassword']
            );
            
            // Créer le cookie JWT
            setcookie(
                'jwt_token',
                $token,
                [
                    'expires' => time() + 900, // 15 minutes
                    'path' => '/',
                    'httponly' => true,
                    'secure' => false, // Pour le développement
                    'samesite' => 'Lax'
                ]
            );

            echo json_encode([
                'success' => true,
                'message' => 'Inscription réussie ! Vous êtes maintenant connecté.',
                'token' => $token
            ]);
        } catch (RegistrationException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function setupTotp(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $token = $_COOKIE['jwt_token'] ?? null;
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            return;
        }

        $user = $this->authService->getUserFromToken($token);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            return;
        }

        try {
            $setup = $this->authService->setupTotp($user->getId());
            
            echo json_encode([
                'success' => true,
                'secret' => $setup['secret'],
                'qr_code_url' => $setup['qr_code_url']
            ]);
        } catch (AuthenticationException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function enableTotp(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $token = $_COOKIE['jwt_token'] ?? null;
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            return;
        }

        $user = $this->authService->getUserFromToken($token);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        
        if (!isset($input['totp_code'])) {
            http_response_code(400);
            echo json_encode(['error' => 'TOTP code required']);
            return;
        }

        try {
            $result = $this->authService->enableTotp($user->getId(), $input['totp_code']);
            echo json_encode([
                'success' => true,
                'message' => 'TOTP enabled successfully'
            ]);
        } catch (AuthenticationException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }

    public function disableTotp(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $token = $_COOKIE['jwt_token'] ?? null;
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            return;
        }

        $user = $this->authService->getUserFromToken($token);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['totp_code'])) {
            http_response_code(400);
            echo json_encode(['error' => 'TOTP code required']);
            return;
        }

        try {
            $this->authService->disableTotp($user->getId(), $input['totp_code']);
            echo json_encode([
                'success' => true,
                'message' => 'TOTP disabled successfully'
            ]);
        } catch (AuthenticationException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function loginPassword(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['username']) || !isset($input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Username and password required']);
            return;
        }

        try {
            $result = $this->authService->beginPasswordLogin($input['username'], $input['password']);

            if ($result['status'] === 'ok') {
                $token = $result['token'];
                setcookie('jwt_token', $token, [
                    'expires' => time() + 900,
                    'path' => '/',
                    'httponly' => true
                ]);

                echo json_encode(['success' => true, 'token' => $token]);
                return;
            }

            if ($result['status'] === 'requires_totp') {
                http_response_code(401);
                echo json_encode(['error' => 'TOTP_REQUIRED', 'requires_totp' => true]);
                return;
            }

            if ($result['status'] === 'requires_otp') {
                $challenge = $this->otpService->startLoginChallenge(
                    $result['user_id'],
                    $result['username'],
                    $result['channel'],
                    $result['phone'] ?? null,
                    $result['email'] ?? null
                );

                http_response_code(401);
                echo json_encode([
                    'requires_otp' => true,
                    'challenge_id' => $challenge['challenge_id'],
                    'expires_at' => $challenge['expires_at'],
                    'channel' => $challenge['channel'],
                    'masked' => $challenge['masked']
                ]);
                return;
            }

        } catch (AuthenticationException $e) {
            http_response_code(401);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function loginOtp(): void
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);

        try {
            $userId = $this->otpService->verify($input['challenge_id'], $input['code']);
            $token = $this->authService->finalizeOtpLogin($userId);

            setcookie('jwt_token', $token, [
                'expires' => time() + 900,
                'path' => '/',
                'httponly' => true
            ]);

            echo json_encode(['success' => true, 'token' => $token]);

        } catch (AuthenticationException $e) {
            http_response_code(401);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function resendOtp(): void
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);

        try {
            $this->otpService->resend($input['challenge_id']);
            echo json_encode(['success' => true]);
        } catch (AuthenticationException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }


    public function startSms(): void
    {
        header('Content-Type: application/json');

        $token = $_COOKIE['jwt_token'] ?? null;
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            return;
        }

        $user = $this->authService->getUserFromToken($token);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['phone'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Phone number required']);
            return;
        }

        $phone = trim($input['phone']);

        try {
            // Enregistre le numéro
            $this->authService->saveUserPhone($user->getId(), $phone);

            $challenge = $this->otpService->startLoginChallenge(
                $user->getId(),
                $user->getUsername(),
                'sms',
                $phone,
                null
            );

            echo json_encode([
                'success' => true,
                'challenge_id' => $challenge['challenge_id'],
                'expires_at' => $challenge['expires_at'],
                'masked' => $challenge['masked']
            ]);
        } catch (AuthenticationException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function verifySms(): void
    {
        header('Content-Type: application/json');

        $token = $_COOKIE['jwt_token'] ?? null;
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            return;
        }

        $user = $this->authService->getUserFromToken($token);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        try {
            $this->otpService->verify($input['challenge_id'], $input['code']);
            $this->authService->activateSms2fa($user->getId());

            echo json_encode(['success' => true]);
        } catch (AuthenticationException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function startEmail(): void
    {
        header('Content-Type: application/json');

        $token = $_COOKIE['jwt_token'] ?? null;
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            return;
        }

        $user = $this->authService->getUserFromToken($token);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            return;
        }

        try {
            $challenge = $this->otpService->startLoginChallenge(
                $user->getId(),
                $user->getUsername(),
                'email',
                null,
                $user->getEmail()
            );

            echo json_encode([
                'success' => true,
                'challenge_id' => $challenge['challenge_id'],
                'expires_at' => $challenge['expires_at'],
                'masked' => $challenge['masked']
            ]);
        } catch (AuthenticationException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function verifyEmail(): void
    {
        header('Content-Type: application/json');

        $token = $_COOKIE['jwt_token'] ?? null;
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            return;
        }

        $user = $this->authService->getUserFromToken($token);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        try {
            $this->otpService->verify($input['challenge_id'], $input['code']);
            $this->authService->activateEmail2fa($user->getId());

            echo json_encode(['success' => true]);
        } catch (AuthenticationException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

