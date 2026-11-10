<?php

namespace App\Http\Controllers\Partner;

use App\Enums\PartnerType;
use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    private function current(Request $request): Partner
    {
        $partner = $request->user()->partner;

        abort_unless($partner, 403);

        return $partner;
    }

    public function edit(Request $request)
    {
        $partner = $this->current($request);

        return view('partner.profile', [
            'partner' => $partner,
            'reviews' => $partner->visibleRatings()->with('user')->latest()->limit(5)->get(),
        ]);
    }

    public function update(Request $request)
    {
        $partner = $this->current($request);

        $request->merge([
            'contact_phone' => PhoneNumber::normalize($request->contact_phone) ?? $request->contact_phone,
        ]);

        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:120'],
            'contact_phone' => ['required', 'regex:/^\+2217[05678]\d{7}$/'],
            'base_lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:base_lng'],
            'base_lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:base_lat'],
            'radius_km' => ['nullable', 'integer', 'between:1,100'],
        ], [
            'contact_phone.regex' => 'Téléphone invalide. Exemple : 77 123 45 67.',
        ]);

        $values = [
            'company_name' => $data['company_name'],
            'contact_phone' => $data['contact_phone'],
            'base_lat' => $data['base_lat'] ?? null,
            'base_lng' => $data['base_lng'] ?? null,
        ];

        if ($partner->type === PartnerType::DEPANNEUR) {
            $values['radius_km'] = $data['radius_km'] ?? $partner->radius_km;
        }

        $partner->update($values);

        return back()->with('status', 'Profil mis à jour.');
    }
}