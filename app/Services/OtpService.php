<?php

namespace App\Services;

use App\Contracts\SmsGatewayInterface;
use App\Models\OtpCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class OtpService
{
    private const MAX_ATTEMPTS = 5;

    private const TTL_MINUTES = 5;

    public function __construct(private SmsGatewayInterface $sms) {}

    public function send(string $phone, string $purpose = 'verify_phone'): void
    {
        $key = "otp-send:{$phone}";

        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages([
                'code' => 'Trop de demandes. Réessayez dans '.RateLimiter::availableIn($key).' secondes.',
            ]);
        }
        RateLimiter::hit($key, 600); // 3 envois max par 10 minutes

        OtpCode::where('phone', $phone)->where('purpose', $purpose)->whereNull('consumed_at')->delete();

        $code = (string) random_int(100000, 999999);

        OtpCode::create([
            'phone' => $phone,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
        ]);

        $this->sms->send($phone, "AutoSur.sn : votre code est {$code}. Valable ".self::TTL_MINUTES.' minutes.');
    }

    public function verify(string $phone, string $code, string $purpose = 'verify_phone'): bool
    {
        $otp = OtpCode::where('phone', $phone)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if (! $otp || $otp->expires_at->isPast() || $otp->attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        $otp->increment('attempts');

        if (! Hash::check($code, $otp->code_hash)) {
            return false;
        }

        $otp->update(['consumed_at' => now()]);

        return true;
    }
}