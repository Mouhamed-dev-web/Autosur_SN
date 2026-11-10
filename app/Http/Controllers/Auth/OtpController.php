<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OtpController extends Controller
{
    private function pendingUser(): ?User
    {
        return User::find(session('otp_user_id'));
    }

    public function show()
    {
        $user = $this->pendingUser();

        return $user
            ? view('auth.verify-otp', ['phone' => $user->phone])
            : redirect()->route('login');
    }

    public function verify(Request $request, OtpService $otp)
    {
        $request->validate(['code' => ['required', 'digits:6']], [
            'code.digits' => 'Le code contient 6 chiffres.',
        ]);

        $user = $this->pendingUser();
        if (! $user) {
            return redirect()->route('login');
        }

        if (! $otp->verify($user->phone, $request->code)) {
            return back()->withErrors(['code' => 'Code invalide ou expiré.']);
        }

        $user->forceFill(['phone_verified_at' => now()])->save();
        session()->forget('otp_user_id');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect($user->homeUrl());
    }

    public function resend(OtpService $otp)
    {
        $user = $this->pendingUser();
        if (! $user) {
            return redirect()->route('login');
        }

        $otp->send($user->phone);

        return back()->with('status', 'Un nouveau code a été envoyé.');
    }
}