<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioSms
{
    public function __construct(
        private Client $twilio
    ) {}

    public function send(string $to, string $body): array
    {
        $from = config('services.twilio.from');

        $message = $this->twilio->messages->create($to, [
            'from' => $from,
            'body' => $body,
        ]);

        return [
            'ok' => true,
            'sid' => $message->sid,
            'to' => $message->to,
            'from' => $message->from,
            'body' => $message->body,
            'status' => $message->status,
        ];
    }
}
