<?php

namespace App\Policies;

use App\Models\SosRequest;
use App\Models\User;

class SosRequestPolicy
{
    /** Le client propriétaire de la demande. */
    public function view(User $user, SosRequest $sos): bool
    {
        return $user->id === $sos->user_id;
    }

    /** Le dépanneur à qui la demande est attribuée. */
    public function manage(User $user, SosRequest $sos): bool
    {
        $partner = $user->partner;

        return $partner !== null && $partner->id === $sos->partner_id;
    }
}