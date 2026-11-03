<?php

namespace App\Services\Sms;

use App\Contracts\SmsGatewayInterface;
use Illuminate\Support\Facades\Log;

class LogSmsGateway implements SmsGatewayInterface
{
    public function send(string $phone, string $message): bool
    {
        Log::info("[SMS vers {$phone}] {$message}");

        return true;
    }
}