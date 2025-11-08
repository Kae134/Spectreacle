<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Notifications\Mail;

interface MailProviderInterface
{
    /**
     * @throws \RuntimeException en cas d'échec d'envoi
     */
    public function send(string $toEmail, string $subject, string $htmlBody, ?string $textBody = null): void;
}
