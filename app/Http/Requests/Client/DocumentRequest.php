<?php

namespace App\Http\Requests\Client;

use App\Enums\DocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // accès contrôlé par le middleware de route et la DocumentPolicy
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'vehicle_id' => $this->vehicle_id ?: null,
            'expires_at' => $this->expires_at ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'title' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::enum(DocumentType::class)],
            // le véhicule doit appartenir à l'utilisateur connecté
            'vehicle_id' => ['nullable', Rule::exists('vehicles', 'id')->where('user_id', $this->user()->id)],
            'expires_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Choisissez un fichier.',
            'file.mimes' => 'Formats acceptés : PDF, JPG et PNG.',
            'file.max' => 'Le fichier ne doit pas dépasser 5 Mo.',
            'file.uploaded' => 'Envoi impossible : fichier trop volumineux pour le serveur.',
            'vehicle_id.exists' => 'Véhicule invalide.',
        ];
    }
}