<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class TwilioService
{
    protected Client $client;
    protected ?string $from;

    public function __construct()
    {
        // $sid   = config('services.twilio.sid');
        // $token = config('services.twilio.token');
        // $this->from = config('services.twilio.from');

        // if (!$sid || !$token) {
        //     throw new \RuntimeException('Twilio credentials are not configured.');
        // }

        // $this->client = new Client($sid, $token);
    }

    public function sendSms(string $to, string $message): array
    {
        $payload = [
            'to'   => $to,
            'from' => $this->from,
            'body' => $message,
        ];

        if (!$this->from) {
            unset($payload['from']);
        }

        Log::info('Sending SMS via Twilio', ['to' => $to]);
        Log::info('SMS Body', context: ['body' => $message]);

        $response = $this->client->messages->create(
            $payload['to'],
            [
                'from' => $payload['from'] ?? null,
                'body' => $payload['body'],
            ]
        );

        \Log::info('SMS sent', [
            'sid'        => $response->sid,
            'status'     => $response->status,
            'to'         => $response->to,
            'from'       => $response->from,
            'price'      => $response->price,
            'price_unit' => $response->priceUnit,
        ]);
        
        return [
            'sid'        => $response->sid,
            'status'     => $response->status,
            'to'         => $response->to,
            'from'       => $response->from,
            'price'      => $response->price,
            'price_unit' => $response->priceUnit,
        ];
    }
}
