<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PartnerRegisterRequest;
use App\Models\Partner;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\DB;

class PartnerRegisterController extends Controller
{
    public function create()
    {
        return view('auth.partner-register');
    }

    public function store(PartnerRegisterRequest $request, OtpService $otp)
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'password' => $data['password'],
            ]);
            $user->assignRole($data['type']);

            Partner::create([
                'user_id' => $user->id,
                'type' => $data['type'],
                'company_name' => $data['company_name'],
                'contact_phone' => $data['contact_phone'],
                'is_validated' => false,   // validé ensuite par l'administration
                'is_available' => false,
                'base_lat' => $data['base_lat'] ?? null,
                'base_lng' => $data['base_lng'] ?? null,
                'radius_km' => $data['radius_km'] ?? 30,
            ]);

            return $user;
        });

        $otp->send($user->phone);
        session(['otp_user_id' => $user->id]);

        return redirect()->route('otp.show')
            ->with('status', 'Un code vous a été envoyé par SMS. Votre dossier sera ensuite examiné par AutoSur.sn.');
    }
}