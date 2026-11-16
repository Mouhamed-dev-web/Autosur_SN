<?php

namespace App\Services\Sms;

use App\Contracts\SmsGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HttpSmsGateway implements SmsGatewayInterface
{
    public function send(string $phone, string $message): bool
    {
        $cfg = config('sms.http');

        if (empty($cfg['url'])) {
            Log::error('SMS : SMS_URL non configurée.');

            return false;
        }

        $payload = array_filter([
            $cfg['fields']['to'] => $cfg['strip_plus'] ? ltrim($phone, '+') : $phone,
            $cfg['fields']['message'] => $message,
            $cfg['fields']['from'] => $cfg['sender'],
        ], fn ($value) => $value !== null && $value !== '');

        try {
            $request = Http::timeout($cfg['timeout'])->acceptJson();

            $request = match ($cfg['auth']) {
                'bearer' => $request->withToken((string) $cfg['token']),
                'header' => $request->withHeaders([$cfg['auth_header'] => (string) $cfg['token']]),
                default => $request,
            };

            $response = $cfg['format'] === 'form'
                ? $request->asForm()->post($cfg['url'], $payload)
                : $request->asJson()->post($cfg['url'], $payload);
        } catch (\Throwable $e) {
            // Jamais le texte du SMS dans les journaux : il contient le code
            Log::error('SMS : erreur réseau', ['to' => $this->mask($phone), 'erreur' => $e->getMessage()]);

            return false;
        }

        if (! $response->successful()) {
            Log::error('SMS : refusé par le fournisseur', [
                'to' => $this->mask($phone),
                'status' => $response->status(),
                'reponse' => mb_substr($response->body(), 0, 300),
            ]);

            return false;
        }

        return true;
    }

    private function mask(string $phone): string
    {
        return substr($phone, 0, 6).'****'.substr($phone, -2);
    }
}