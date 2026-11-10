<?php

namespace App\Http\Requests;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PartnerRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => PhoneNumber::normalize($this->phone) ?? $this->phone,
            'contact_phone' => PhoneNumber::normalize($this->contact_phone) ?? $this->contact_phone,
            'email' => $this->email ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            // jamais « admin » ni « client » : seuls ces trois types sont acceptés
            'type' => ['required', Rule::in(['garage', 'depanneur', 'assurance'])],
            'company_name' => ['required', 'string', 'max:120'],
            'contact_phone' => ['required', 'regex:/^\+2217[05678]\d{7}$/'],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'regex:/^\+2217[05678]\d{7}$/', 'unique:users,phone'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'base_lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:base_lng'],
            'base_lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:base_lat'],
            'radius_km' => ['nullable', 'integer', 'between:1,100'],
            'terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Type de partenaire invalide.',
            'phone.regex' => 'Numéro invalide. Exemple : 77 123 45 67.',
            'phone.unique' => 'Ce numéro est déjà utilisé.',
            'contact_phone.regex' => 'Téléphone professionnel invalide. Exemple : 77 123 45 67.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
            'terms.accepted' => 'Vous devez accepter que votre dossier soit examiné par AutoSur.sn.',
        ];
    }
}