<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Notifications\Mail;

use Spectreacle\Infrastructure\Notifications\Mail\MailProviderInterface;

class LogMailProvider implements MailProviderInterface
{
    public function send(string $toEmail, string $subject, string $htmlBody, ?string $textBody = null): void
    {
        // Pour dev, log seulement :
        error_log("[MAIL] to={$toEmail} subj={$subject} text=" . ($textBody ?? strip_tags($htmlBody)));
    }
}