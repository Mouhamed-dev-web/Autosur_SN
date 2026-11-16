<?php

namespace Tests\Support;

use App\Contracts\SmsGatewayInterface;

class FakeSmsGateway implements SmsGatewayInterface
{
    /** @var array<int, array{0: string, 1: string}> */
    public array $sent = [];

    public function send(string $phone, string $message): bool
    {
        $this->sent[] = [$phone, $message];

        return true;
    }

    /** Dernier code à 6 chiffres envoyé par SMS. */
    public function lastCode(): string
    {
        preg_match('/(\d{6})/', end($this->sent)[1], $m);

        return $m[1];
    }

    /** Numéro du dernier SMS envoyé. */
    public function lastPhone(): string
    {
        return end($this->sent)[0];
    }
}