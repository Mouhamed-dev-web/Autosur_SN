<?php

namespace App\Http\Requests;

use App\Models\ContactMessage;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->email ?: null,
            'phone' => $this->phone ? (PhoneNumber::normalize($this->phone) ?? $this->phone) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255', 'required_without:phone'],
            'phone' => ['nullable', 'regex:/^\+2217[05678]\d{7}$/', 'required_without:email'],
            'subject' => ['required', Rule::in(array_keys(ContactMessage::SUBJECTS))],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            // champ piège : un humain ne le remplit jamais
            'website' => ['nullable', 'max:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Indiquez un e-mail ou un téléphone pour que nous puissions vous répondre.',
            'phone.required_without' => 'Indiquez un e-mail ou un téléphone pour que nous puissions vous répondre.',
            'phone.regex' => 'Numéro invalide. Exemple : 77 123 45 67.',
            'message.min' => 'Votre message est trop court.',
        ];
    }
}