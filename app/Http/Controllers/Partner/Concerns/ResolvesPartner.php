<?php

namespace App\Http\Controllers\Partner\Concerns;

use App\Enums\PartnerType;
use App\Models\Partner;
use Illuminate\Http\Request;

trait ResolvesPartner
{
    /** Le partenaire connecté, du type attendu, sinon 403. */
    protected function partner(Request $request, PartnerType $type): Partner
    {
        $partner = $request->user()->partner;

        abort_unless($partner && $partner->type === $type, 403);

        return $partner;
    }
}