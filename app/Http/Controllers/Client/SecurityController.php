<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecurityController extends Controller
{
    private const PURPOSE = 'change_phone';

    public function edit(Request $request)
    {
        return view('client.security', [
            'user' => $request->user(),
            'pendingPhone' => session('pending_phone'),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
            'password.different' => 'Le nouveau mot de passe doit être différent de l\'ancien.',
        ]);

        $user = $request->user();
        $user->update(['password' => $data['password']]);

        // Déconnecte les autres appareils, garde celui-ci
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        return back()->with('status', 'Mot de passe modifié. Vos autres appareils ont été déconnectés.');
    }

    public function requestPhone(Request $request, OtpService $otp)
    {
        $request->merge(['new_phone' => PhoneNumber::normalize($request->new_phone) ?? $request->new_phone]);

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_phone' => ['required', 'regex:/^\+2217[05678]\d{7}$/', 'unique:users,phone'],
        ], [
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
            'new_phone.regex' => 'Numéro invalide. Exemple : 77 123 45 67.',
            'new_phone.unique' => 'Ce numéro est déjà utilisé.',
        ]);

        session(['pending_phone' => $data['new_phone']]);
        $otp->send($data['new_phone'], self::PURPOSE);

        return back()->with('status', 'Un code a été envoyé au nouveau numéro.');
    }

    public function confirmPhone(Request $request, OtpService $otp)
    {
        $data = $request->validate(['code' => ['required', 'digits:6']], [
            'code.digits' => 'Le code contient 6 chiffres.',
        ]);

        $phone = session('pending_phone');

        if (! $phone) {
            return back()->withErrors(['code' => 'Aucun changement de numéro en cours.']);
        }

        if (User::where('phone', $phone)->exists()) {
            session()->forget('pending_phone');

            return back()->withErrors(['code' => 'Ce numéro est déjà utilisé.']);
        }

        if (! $otp->verify($phone, $data['code'], self::PURPOSE)) {
            return back()->withErrors(['code' => 'Code invalide ou expiré.']);
        }

        $request->user()->forceFill(['phone' => $phone, 'phone_verified_at' => now()])->save();
        session()->forget('pending_phone');

        return back()->with('status', 'Numéro de téléphone modifié.');
    }

    public function cancelPhone()
    {
        session()->forget('pending_phone');

        return back();
    }
}