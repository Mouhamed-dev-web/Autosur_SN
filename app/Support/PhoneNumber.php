<?php

namespace App\Support;

class PhoneNumber
{
    public static function normalize(?string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $raw);

        if (str_starts_with($digits, '00221')) {
            $digits = substr($digits, 5);
        } elseif (str_starts_with($digits, '221')) {
            $digits = substr($digits, 3);
        }

        return preg_match('/^7[05678]\d{7}$/', $digits) ? '+221'.$digits : null;
    }
}