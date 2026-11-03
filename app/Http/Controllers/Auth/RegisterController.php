<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\OtpService;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request, OtpService $otp)
    {
        $user = User::create($request->safe()->only(['name', 'phone', 'email', 'password']));
        $user->assignRole(RoleEnum::CLIENT->value);

        $otp->send($user->phone);
        session(['otp_user_id' => $user->id]);

        return redirect()->route('otp.show')->with('status', 'Un code vous a été envoyé par SMS.');
    }
}