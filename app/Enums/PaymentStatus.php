<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';
    case EXPIRED = 'expired';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::PAID => 'Payé',
            self::FAILED => 'Échoué',
            self::EXPIRED => 'Expiré',
            self::REFUNDED => 'Remboursé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'bg-orange-100 text-orange-800',
            self::PAID => 'bg-emerald-100 text-emerald-800',
            self::FAILED => 'bg-red-100 text-red-800',
            self::EXPIRED => 'bg-gray-100 text-gray-600',
            self::REFUNDED => 'bg-blue-100 text-blue-800',
        };
    }
}