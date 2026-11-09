<?php

namespace App\Policies;

use App\Models\TechnicalVisit;
use App\Models\User;

class TechnicalVisitPolicy
{
    public function view(User $user, TechnicalVisit $visit): bool
    {
        return $user->id === $visit->user_id;
    }

    public function cancel(User $user, TechnicalVisit $visit): bool
    {
        return $user->id === $visit->user_id;
    }
    public function pay(User $user, TechnicalVisit $visit): bool
    {
        return $user->id === $visit->user_id;
    }
}