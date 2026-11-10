<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactReceived extends Mailable
{
    public function __construct(public ContactMessage $contact) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Contact AutoSur.sn : '.$this->contact->subjectLabel());
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-received');
    }
}