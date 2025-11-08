<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Notifications\Sms;

use Spectreacle\Infrastructure\Notifications\Sms\SmsProviderInterface;
use Twilio\Rest\Client;

class TwilioSmsProvider implements SmsProviderInterface
{
    private Client $client;

    public function __construct(
        string $sid,
        string $token,
        private string $from
    ) {
        $this->client = new Client($sid, $token);
    }

    public function send(string $toE164, string $message): void
    {
        $this->client->messages->create($toE164, [
            'from' => $this->from,
            'body' => $message,
        ]);
    }
}
