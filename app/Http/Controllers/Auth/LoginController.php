<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request, OtpService $otp)
    {
        $data = $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = strtolower($data['identifier']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'identifier' => 'Trop de tentatives. Réessayez dans '.RateLimiter::availableIn($throttleKey).' secondes.',
            ]);
        }

        $identifier = $data['identifier'];
        if (str_contains($identifier, '@')) {
            $credentials = ['email' => $identifier];
        } else {
            $credentials = ['phone' => PhoneNumber::normalize($identifier) ?? $identifier];
        }

        if (! Auth::attempt($credentials + ['password' => $data['password']], $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'identifier' => 'Identifiants incorrects.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $user = Auth::user();

        // Téléphone non vérifié : on déconnecte et on relance la vérification
        if ($user->phone && ! $user->phone_verified_at) {
            Auth::logout();
            session(['otp_user_id' => $user->id]);
            $otp->send($user->phone);

            return redirect()->route('otp.show')->with('status', 'Vérifiez votre numéro avec le code reçu par SMS.');
        }

        $request->session()->regenerate();

        return redirect($this->homeFor($user));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function homeFor(User $user): string
    {
        return match (true) {
            $user->hasRole(RoleEnum::ADMIN->value) => '/admin',
            $user->hasAnyRole([RoleEnum::GARAGE->value, RoleEnum::DEPANNEUR->value, RoleEnum::ASSURANCE->value]) => route('partner.dashboard'),
            default => route('client.dashboard'),
        };
    }
}