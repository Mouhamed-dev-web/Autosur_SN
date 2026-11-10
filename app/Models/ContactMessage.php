<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'subject', 'message', 'ip', 'handled_at'];

    protected function casts(): array
    {
        return ['handled_at' => 'datetime'];
    }

    public const SUBJECTS = [
        'question' => 'Question générale',
        'support' => 'Aide sur mon compte',
        'partner' => 'Devenir partenaire',
        'other' => 'Autre',
    ];

    public function subjectLabel(): string
    {
        return self::SUBJECTS[$this->subject] ?? $this->subject;
    }
}