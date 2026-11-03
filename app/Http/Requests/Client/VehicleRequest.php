<?php

namespace App\Http\Requests\Client;

use App\Enums\InsuranceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // l'accès est contrôlé par le middleware de route et la VehiclePolicy
    }

    protected function prepareForValidation(): void
    {
        $plate = mb_strtoupper(trim(preg_replace('/\s+/', ' ', (string) $this->plate)));
        $vin = mb_strtoupper(trim((string) $this->vin));

        $this->merge([
            'plate' => $plate,
            'vin' => $vin !== '' ? $vin : null,
            'color' => $this->color ?: null,
        ]);
    }

    public function rules(): array
    {
        $vehicle = $this->route('vehicle'); // null à la création

        return [
            'plate' => [
                'required',
                'regex:/^[A-Z0-9][A-Z0-9 \-]{2,18}[A-Z0-9]$/',
                Rule::unique('vehicles', 'plate')->ignore($vehicle),
            ],
            'brand' => ['required', 'string', 'max:60'],
            'model' => ['required', 'string', 'max:60'],
            'year' => ['required', 'integer', 'between:1950,'.(now()->year + 1)],
            'color' => ['nullable', 'string', 'max:40'],
            'vin' => ['nullable', 'alpha_num', 'max:17', Rule::unique('vehicles', 'vin')->ignore($vehicle)],
            'insurance_status' => ['required', Rule::enum(InsuranceStatus::class)],
            'insurance_expires_at' => ['nullable', 'date'],
            'technical_visit_last_at' => ['nullable', 'date'],
            'technical_visit_expires_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'plate.regex' => 'Immatriculation invalide. Exemple : DK-1234-AB.',
            'plate.unique' => 'Cette immatriculation est déjà enregistrée.',
            'vin.unique' => 'Ce numéro de châssis est déjà enregistré.',
            'vin.alpha_num' => 'Le numéro de châssis ne contient que des lettres et des chiffres.',
            'year.between' => 'Année invalide.',
        ];
    }
}