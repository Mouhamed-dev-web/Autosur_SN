<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\AccountService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function show(Request $request, AccountService $accounts)
    {
        return view('client.delete-account', [
            'blockers' => $accounts->blockers($request->user()),
        ]);
    }

    public function destroy(Request $request, AccountService $accounts)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'confirmation' => ['required', 'in:SUPPRIMER'],
        ], [
            'current_password.current_password' => 'Le mot de passe est incorrect.',
            'confirmation.in' => 'Tapez le mot SUPPRIMER pour confirmer.',
        ]);

        $user = $request->user();

        try {
            Auth::logout();
            $accounts->delete($user);
        } catch (DomainException $e) {
            Auth::login($user);

            return back()->withErrors(['account' => $e->getMessage()]);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Votre compte et vos données ont été supprimés.');
    }
}