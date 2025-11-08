<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Notifications\Sms;

use Spectreacle\Infrastructure\Notifications\Sms\SmsProviderInterface;

class LogSmsProvider implements SmsProviderInterface
{
    public function send(string $toE164, string $message): void
    {
        error_log("[SMS] {$toE164} :: {$message}");
    }
}