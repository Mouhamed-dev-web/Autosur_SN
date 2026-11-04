<?php

namespace App\Http\Requests\Client;

use App\Enums\CoverType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InsuranceQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // accès contrôlé par le middleware de route et la policy
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['vehicle_value' => $this->vehicle_value ?: null]);
    }

    public function rules(): array
    {
        return [
            // le véhicule doit appartenir à l'utilisateur connecté
            'vehicle_id' => ['required', Rule::exists('vehicles', 'id')->where('user_id', $this->user()->id)],
            'cover_type' => ['required', Rule::enum(CoverType::class)],
            'fiscal_power' => ['required', 'integer', 'between:1,50'],
            'vehicle_value' => [
                Rule::requiredIf($this->cover_type === CoverType::TOUS_RISQUES->value),
                'nullable', 'integer', 'between:100000,500000000',
            ],
            'duration_months' => ['required', Rule::in([3, 6, 12])],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_id.exists' => 'Véhicule invalide.',
            'fiscal_power.between' => 'La puissance fiscale doit être comprise entre 1 et 50 CV.',
            'vehicle_value.required' => 'Indiquez la valeur du véhicule pour une assurance tous risques.',
            'vehicle_value.between' => 'Valeur invalide (entre 100 000 et 500 000 000 FCFA).',
            'duration_months.in' => 'Durée invalide.',
        ];
    }
}