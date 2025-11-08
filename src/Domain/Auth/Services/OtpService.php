<?php

declare(strict_types=1);

namespace Spectreacle\Application\Auth\Services;

use DateInterval;
use DateTimeImmutable;
use Spectreacle\Domain\Auth\Repositories\OtpChallengeRepositoryInterface;
use Spectreacle\Infrastructure\Notifications\Mail\MailProviderInterface;
use Spectreacle\Infrastructure\Notifications\Sms\SmsProviderInterface;
use Spectreacle\Shared\Exceptions\AuthenticationException;

class OtpService
{
    public const CODE_DIGITS = 6;
    public const TTL_SECONDS = 300;        // 5 minutes
    public const MAX_ATTEMPTS = 5;         // anti bruteforce
    public const RESEND_COOLDOWN = 30;     // seconds
    public const RATE_LIMIT_WINDOW = 3600; // 1h
    public const RATE_LIMIT_MAX = 5;       // max OTP envoyés / h / user

    public function __construct(
        private OtpChallengeRepositoryInterface $repo,
        private ?SmsProviderInterface $sms = null,
        private ?MailProviderInterface $mail = null,
    ) {}

    /**
     * @return array{challenge_id:string, expires_at:int, channel:string, masked:string}
     */
    public function startLoginChallenge(
        int $userId,
        string $username,
        string $channel,           // 'sms' | 'email'
        ?string $phoneE164,
        ?string $email
    ): array {
        $channel = strtolower($channel);
        if (!in_array($channel, ['sms','email'], true)) {
            throw new AuthenticationException('Unsupported channel');
        }

        // Rate-limit global par user/purpose
        $since = time() - self::RATE_LIMIT_WINDOW;
        if ($this->repo->countRecentForUser($userId, 'login', $since) >= self::RATE_LIMIT_MAX) {
            throw new AuthenticationException('Too many OTP requests, try later');
        }

        // Génération code et hachage
        $code = $this->generateNumericCode();
        $codeHash = password_hash($code, PASSWORD_ARGON2ID);

        $now = time();
        $expiresAt = $now + self::TTL_SECONDS;
        $challengeId = $this->repo->create([
            'user_id'     => $userId,
            'purpose'     => 'login',
            'channel'     => $channel,
            'code_hash'   => $codeHash,
            'created_at'  => $now,
            'expires_at'  => $expiresAt,
            'attempts'    => 0,
            'last_send'   => $now,
            // Optionnel : bind à IP/user-agent si tu veux
        ]);

        // Envoi
        if ($channel === 'sms') {
            if (!$this->sms || !$phoneE164) {
                throw new AuthenticationException('SMS not available');
            }
            $this->sms->send($phoneE164, "Votre code Spectreacle : {$code}");
            $masked = $this->maskPhone($phoneE164);
        } else {
            if (!$this->mail || !$email) {
                throw new AuthenticationException('Email not available');
            }
            $subject = 'Votre code de connexion Spectreacle';
            $bodyText = "Code : {$code} (expire dans 5 minutes)";
            $bodyHtml = "<p>Votre code :</p><h2>{$code}</h2><p>Expire dans 5 minutes.</p>";
            $this->mail->send($email, $subject, $bodyHtml, $bodyText);
            $masked = $this->maskEmail($email);
        }

        return [
            'challenge_id' => $challengeId,
            'expires_at'   => $expiresAt,
            'channel'      => $channel,
            'masked'       => $masked,
        ];
    }

    public function resend(string $challengeId): void
    {
        $c = $this->challengeOrFail($challengeId);
        if (time() < ($c['last_send'] + self::RESEND_COOLDOWN)) {
            throw new AuthenticationException('Please wait before resending');
        }
        if ($c['expires_at'] <= time()) {
            throw new AuthenticationException('Challenge expired');
        }

        // Impossible de récupérer le code : on régénère un nouveau code et hache
        $newCode = $this->generateNumericCode();
        $this->repo->update($challengeId, [
            'code_hash' => password_hash($newCode, PASSWORD_ARGON2ID),
            'last_send' => time(),
        ]);

        if ($c['channel'] === 'sms') {
            $this->sms?->send($c['phone'] ?? '', "Votre code Spectreacle : {$newCode}");
        } else {
            $sub = 'Nouveau code Spectreacle';
            $html = "<p>Nouveau code :</p><h2>{$newCode}</h2><p>Expire dans 5 minutes.</p>";
            $this->mail?->send($c['email'] ?? '', $sub, $html, "Code : {$newCode}");
        }
    }

    public function verify(string $challengeId, string $code): int
    {
        $c = $this->challengeOrFail($challengeId);

        if ($c['expires_at'] <= time()) {
            $this->repo->delete($challengeId);
            throw new AuthenticationException('Code expired');
        }
        if ($c['attempts'] >= self::MAX_ATTEMPTS) {
            $this->repo->delete($challengeId);
            throw new AuthenticationException('Too many attempts');
        }

        $clean = preg_replace('/\D+/', '', $code);
        if (!$clean || strlen($clean) > 10) {
            $this->repo->update($challengeId, ['attempts' => $c['attempts'] + 1]);
            throw new AuthenticationException('Invalid code');
        }

        $ok = password_verify($clean, $c['code_hash']);
        $this->repo->update($challengeId, ['attempts' => $c['attempts'] + 1]);

        if (!$ok) {
            if ($c['attempts'] + 1 >= self::MAX_ATTEMPTS) {
                $this->repo->delete($challengeId);
            }
            throw new AuthenticationException('Invalid code');
        }

        // SUCCESS → one-time
        $this->repo->delete($challengeId);
        return (int)$c['user_id'];
    }

    private function challengeOrFail(string $challengeId): array
    {
        $c = $this->repo->find($challengeId);
        if (!$c) {
            throw new AuthenticationException('Challenge not found');
        }
        return $c;
    }

    private function generateNumericCode(): string
    {
        // 000000–999999
        return str_pad((string)random_int(0, 999999), self::CODE_DIGITS, '0', STR_PAD_LEFT);
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = explode('@', $email, 2);
        $nameMasked = substr($name, 0, 1) . str_repeat('•', max(strlen($name) - 2, 1)) . substr($name, -1);
        return $nameMasked . '@' . $domain;
    }

    private function maskPhone(string $e164): string
    {
        // +33612345678 -> +33•••••5678
        return substr($e164, 0, 3) . str_repeat('•', max(strlen($e164) - 7, 3)) . substr($e164, -4);
    }
}
