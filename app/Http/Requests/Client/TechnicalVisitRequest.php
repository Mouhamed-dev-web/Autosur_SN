<?php

namespace App\Http\Requests\Client;

use App\Enums\VisitStatus;
use App\Models\TechnicalVisit;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TechnicalVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // accès contrôlé par le middleware de route et les règles ci-dessous
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['notes' => $this->notes ?: null]);
    }

    public function rules(): array
    {
        return [
            // le véhicule doit appartenir à l'utilisateur connecté
            'vehicle_id' => [
                'required',
                Rule::exists('vehicles', 'id')->where('user_id', $this->user()->id),
                function (string $attribute, mixed $value, \Closure $fail) {
                    $active = TechnicalVisit::where('vehicle_id', $value)
                        ->whereIn('status', [VisitStatus::REQUESTED->value, VisitStatus::CONFIRMED->value])
                        ->exists();

                    if ($active) {
                        $fail('Une visite est déjà prévue pour ce véhicule. Annulez-la avant d\'en demander une autre.');
                    }
                },
            ],
            'scheduled_at' => [
                'required',
                'date',
                'after:now',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $at = Carbon::parse($value);
                    $open = config('autosur.technical_visit.open_hour');
                    $close = config('autosur.technical_visit.close_hour');

                    if ($at->isSunday() || $at->hour < $open || $at->hour >= $close) {
                        $fail("Rendez-vous possibles du lundi au samedi, de {$open}h à {$close}h.");
                    }
                },
            ],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_id.exists' => 'Véhicule invalide.',
            'scheduled_at.after' => 'Choisissez une date et une heure à venir.',
        ];
    }
}