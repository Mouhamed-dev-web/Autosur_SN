<?php

namespace App\Contracts;

interface SmsGatewayInterface
{
    public function send(string $phone, string $message): bool;
}