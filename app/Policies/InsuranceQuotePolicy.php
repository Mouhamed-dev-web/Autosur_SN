<?php

namespace App\Policies;

use App\Models\InsuranceQuote;
use App\Models\User;

class InsuranceQuotePolicy
{
    public function view(User $user, InsuranceQuote $quote): bool
    {
        return $user->id === $quote->user_id;
    }
}