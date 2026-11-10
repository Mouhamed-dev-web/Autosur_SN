<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Mail\ContactReceived;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function create(Request $request)
    {
        $subject = $request->query('sujet');

        return view('web.contact', [
            'subjects' => ContactMessage::SUBJECTS,
            'selected' => array_key_exists((string) $subject, ContactMessage::SUBJECTS) ? $subject : 'question',
        ]);
    }

    public function store(ContactRequest $request)
    {
        $contact = ContactMessage::create($request->safe()->only(['name', 'email', 'phone', 'subject', 'message']) + [
            'ip' => $request->ip(),
        ]);

        $to = config('autosur.contact_email');

        if ($to) {
            try {
                Mail::to($to)->send(new ContactReceived($contact));
            } catch (\Throwable $e) {
                // Le message reste enregistré en base : on ne le perd pas
                Log::error('Envoi du message de contact échoué', ['id' => $contact->id, 'erreur' => $e->getMessage()]);
            }
        }

        return redirect()->route('contact')->with('status', 'Merci ! Votre message a bien été envoyé. Nous vous répondrons rapidement.');
    }
}