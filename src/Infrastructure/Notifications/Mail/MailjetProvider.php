<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Notifications\Mail;

use Spectreacle\Infrastructure\Notifications\Mail\MailProviderInterface;
use Mailjet\Client;
use Mailjet\Resources;

class MailjetProvider implements MailProviderInterface
{
    private Client $client;

    public function __construct(
        string $apiKey,
        string $apiSecret,
        private string $fromEmail,
        private string $fromName
    ) {
        $this->client = new Client($apiKey, $apiSecret, true, ['version' => 'v3.1']);
    }

    public function send(string $toEmail, string $subject, string $htmlBody, ?string $textBody = null): void
    {
        $body = [
            'Messages' => [
                [
                    'From'     => ['Email' => $this->fromEmail, 'Name' => $this->fromName],
                    'To'       => [['Email' => $toEmail]],
                    'Subject'  => $subject,
                    'TextPart' => $textBody ?? strip_tags($htmlBody),
                    'HTMLPart' => $htmlBody,
                ]
            ]
        ];

        $this->client->post(Resources::$Email, ['body' => $body]);
    }
}
