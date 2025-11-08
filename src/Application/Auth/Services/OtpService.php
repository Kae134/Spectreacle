<?php

declare(strict_types=1);

namespace Spectreacle\Application\Auth\Services;

use DateTimeImmutable;
use Spectreacle\Domain\Auth\Repositories\OtpChallengeRepositoryInterface;
use Spectreacle\Infrastructure\Notifications\Mail\MailProviderInterface;
use Spectreacle\Infrastructure\Notifications\Sms\SmsProviderInterface;
use Spectreacle\Shared\Exceptions\AuthenticationException;

class OtpService
{
    public const CODE_DIGITS = 6;
    public const TTL_SECONDS = 300;
    public const MAX_ATTEMPTS = 5;
    public const RESEND_COOLDOWN = 30;
    public const RATE_LIMIT_WINDOW = 3600;
    public const RATE_LIMIT_MAX = 5;

    public function __construct(
        private OtpChallengeRepositoryInterface $repo,
        private ?SmsProviderInterface $sms = null,
        private ?MailProviderInterface $mail = null,
    ) {}

    private function generateNumericCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function maskPhone(string $phone): string
    {
        return substr($phone, 0, 3)
            . str_repeat('•', max(strlen($phone) - 7, 3))
            . substr($phone, -4);
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = explode('@', $email, 2);
        return substr($name, 0, 1)
            . str_repeat('•', max(strlen($name) - 2, 1))
            . substr($name, -1)
            . '@'
            . $domain;
    }

    public function startLoginChallenge(
        int $userId,
        string $username,
        string $channel,
        ?string $phone,
        ?string $email
    ): array {
        $channel = strtolower($channel);
        if (!in_array($channel, ['sms', 'email'], true)) {
            throw new AuthenticationException("Unsupported channel");
        }

        $since = time() - self::RATE_LIMIT_WINDOW;
        if ($this->repo->countRecentForUser($userId, 'login', $since) >= self::RATE_LIMIT_MAX) {
            throw new AuthenticationException("Too many OTP requests");
        }

        $code = $this->generateNumericCode();
        $hash = password_hash($code, PASSWORD_ARGON2ID);

        $challengeId = $this->repo->create([
            'user_id'     => $userId,
            'purpose'     => 'login',
            'channel'     => $channel,
            'code_hash'   => $hash,
            'created_at'  => time(),
            'expires_at'  => time() + self::TTL_SECONDS,
            'attempts'    => 0,
            'last_send'   => time(),
            'phone'       => $phone,
            'email'       => $email
        ]);

        // Envoi
        if ($channel === 'sms') {
            if (!$this->sms) {
                throw new AuthenticationException("SMS provider unavailable");
            }
            $this->sms->send($phone, "Code de connexion Spectreacle : $code");
            $masked = $this->maskPhone($phone);
        } else {
            if (!$this->mail) {
                throw new AuthenticationException("Email provider unavailable");
            }
            $this->mail->send($email, "Code de connexion", "<p>Votre code est : <strong>$code</strong></p>");
            $masked = $this->maskEmail($email);
        }

        return [
            'challenge_id' => $challengeId,
            'expires_at'   => time() + self::TTL_SECONDS,
            'channel'      => $channel,
            'masked'       => $masked
        ];
    }

    public function resend(string $id): void
    {
        $c = $this->repo->find($id);
        if (!$c) throw new AuthenticationException("Challenge not found");

        if (time() < $c['last_send'] + self::RESEND_COOLDOWN) {
            throw new AuthenticationException("Wait before resending");
        }

        if ($c['expires_at'] <= time()) {
            throw new AuthenticationException("Challenge expired");
        }

        $code = $this->generateNumericCode();
        $hash = password_hash($code, PASSWORD_ARGON2ID);

        $this->repo->update($id, [
            'code_hash' => $hash,
            'last_send' => time()
        ]);

        if ($c['channel'] === 'sms') {
            $this->sms->send($c['phone'], "Code de connexion Spectreacle : $code");
        } else {
            $this->mail->send($c['email'], "Code de connexion", "<p>Votre code est : <strong>$code</strong></p>");
        }
    }

    public function verify(string $id, string $code): int
    {
        $c = $this->repo->find($id);
        if (!$c) throw new AuthenticationException("Challenge not found");

        if ($c['expires_at'] <= time()) {
            $this->repo->delete($id);
            throw new AuthenticationException("Code expired");
        }

        if ($c['attempts'] >= self::MAX_ATTEMPTS) {
            $this->repo->delete($id);
            throw new AuthenticationException("Too many attempts");
        }

        $clean = preg_replace('/\D+/', '', $code);
        $ok = password_verify($clean, $c['code_hash']);

        $this->repo->update($id, ['attempts' => $c['attempts'] + 1]);

        if (!$ok) {
            if ($c['attempts'] + 1 >= self::MAX_ATTEMPTS) {
                $this->repo->delete($id);
            }
            throw new AuthenticationException("Invalid code");
        }

        $this->repo->delete($id);

        return (int) $c['user_id'];
    }
}
