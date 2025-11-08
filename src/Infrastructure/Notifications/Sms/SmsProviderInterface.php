<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Notifications\Sms;

interface SmsProviderInterface
{
    /**
     * @throws \RuntimeException en cas d'échec d'envoi
     */
    public function send(string $toE164, string $message): void;
}