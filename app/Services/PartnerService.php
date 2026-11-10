<?php

namespace App\Services;

use App\Models\Partner;
use App\Notifications\StatusNotification;

class PartnerService
{
    public function validate(Partner $partner): Partner
    {
        $partner->update(['is_validated' => true]);

        $partner->user->notify(new StatusNotification(
            'Compte partenaire',
            'Votre compte AutoSur.sn est validé. Vous pouvez maintenant recevoir et traiter des demandes.',
            route('partner.dashboard'),
        ));

        return $partner;
    }

    /** Suspension : le partenaire ne reçoit plus rien et passe indisponible. */
    public function suspend(Partner $partner): Partner
    {
        $partner->update(['is_validated' => false, 'is_available' => false]);

        $partner->user->notify(new StatusNotification(
            'Compte partenaire',
            'Votre compte AutoSur.sn est suspendu. Contactez-nous pour plus d\'informations.',
            route('partner.dashboard'),
        ));

        return $partner;
    }
}