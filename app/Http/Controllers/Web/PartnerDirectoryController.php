<?php

namespace App\Http\Controllers\Web;

use App\Enums\PartnerType;
use App\Http\Controllers\Controller;
use App\Models\Partner;

class PartnerDirectoryController extends Controller
{
    public function index()
    {
        $partners = Partner::where('is_validated', true)
            ->withAvg('visibleRatings as ratings_avg', 'score')
            ->withCount('visibleRatings as ratings_count')
            ->orderBy('company_name')
            ->get()
            ->groupBy(fn (Partner $p) => $p->type->value);

        return view('web.partners', [
            'partners' => $partners,
            'types' => PartnerType::cases(),
        ]);
    }
}