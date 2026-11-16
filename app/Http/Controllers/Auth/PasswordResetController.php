<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    private const PURPOSE = 'reset_password';

    public function request()
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request, OtpService $otp)
    {
        $request->merge(['phone' => PhoneNumber::normalize($request->phone) ?? $request->phone]);
        $request->validate(
            ['phone' => ['required', 'regex:/^\+2217[05678]\d{7}$/']],
            ['phone.regex' => 'Numéro invalide. Exemple : 77 123 45 67.'],
        );

        // Réponse identique que le numéro existe ou non : on ne révèle jamais les comptes
        if (User::where('phone', $request->phone)->exists()) {
            try {
                $otp->send($request->phone, self::PURPOSE);
            } catch (ValidationException) {
                // limite de fréquence atteinte : on ne le signale pas non plus
            }
        }

        session(['reset_phone' => $request->phone]);

        return redirect()->route('password.reset.form')
            ->with('status', 'Si ce numéro est enregistré, un code vient de lui être envoyé par SMS.');
    }

    public function form()
    {
        if (! session('reset_phone')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password');
    }

    public function reset(Request $request, OtpService $otp)
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'code.digits' => 'Le code contient 6 chiffres.',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
        ]);

        $phone = session('reset_phone');
        $user = $phone ? User::where('phone', $phone)->first() : null;

        if (! $user || ! $otp->verify($phone, $data['code'], self::PURPOSE)) {
            return back()->withErrors(['code' => 'Code invalide ou expiré.']);
        }

        $user->forceFill([
            'password' => $data['password'],
            'remember_token' => Str::random(60),
        ])->save();

        // Déconnecte tous les appareils
        DB::table('sessions')->where('user_id', $user->id)->delete();
        session()->forget('reset_phone');

        return redirect()->route('login')->with('status', 'Mot de passe modifié. Vous pouvez vous connecter.');
    }
}