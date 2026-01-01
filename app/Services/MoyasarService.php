<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MoyasarService
{
    private string $baseUrl = 'https://api.moyasar.com/v1';

    public function __construct(private string $secretKey) {}

    public function createInvoice(array $payload): array
    {
        $res = Http::withBasicAuth($this->secretKey, '')
            ->acceptJson()
            ->asJson()
            ->post($this->baseUrl.'/invoices', $payload);

        if (!$res->successful()) {
            throw new \RuntimeException('Moyasar error: '.$res->body());
        }

        return $res->json();
    }

    public function fetchPayment(string $paymentId): array
    {
        $res = Http::withBasicAuth($this->secretKey, '')
            ->acceptJson()
            ->get($this->baseUrl.'/payments/'.$paymentId);

        if (!$res->successful()) {
            throw new \RuntimeException('Moyasar error: '.$res->body());
        }

        return $res->json();
    }
}