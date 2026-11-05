<?php

namespace App\Http\Requests\Client;

use App\Enums\ProblemType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SosRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // accès contrôlé par le middleware de route
    }

    public function rules(): array
    {
        return [
            // le véhicule doit appartenir à l'utilisateur connecté
            'vehicle_id' => ['required', Rule::exists('vehicles', 'id')->where('user_id', $this->user()->id)],
            'problem' => ['required', Rule::enum(ProblemType::class)],
            'description' => ['nullable', 'string', 'max:500'],
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
            // sans position GPS, le lieu décrit est obligatoire
            'location_text' => ['nullable', 'string', 'max:255', 'required_without_all:lat,lng'],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_id.exists' => 'Véhicule invalide.',
            'location_text.required_without_all' => 'Autorisez la géolocalisation, ou décrivez où vous êtes (quartier, route, repère).',
            'lat.between' => 'Position invalide.',
            'lng.between' => 'Position invalide.',
        ];
    }
}