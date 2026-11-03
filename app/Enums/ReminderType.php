<?php

namespace App\Enums;

enum ReminderType: string
{
    case INSURANCE = 'insurance';
    case TECHNICAL_VISIT = 'technical_visit';
    case DOCUMENT = 'document';

    public function label(): string
    {
        return match ($this) {
            self::INSURANCE => 'Assurance',
            self::TECHNICAL_VISIT => 'Visite technique',
            self::DOCUMENT => 'Document',
        };
    }
}