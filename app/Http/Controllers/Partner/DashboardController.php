<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\PartnerDashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, PartnerDashboardService $dashboard)
    {
        $partner = $request->user()->partner;

        abort_unless($partner, 403);

        return view('partner.dashboard', $dashboard->for($partner));
    }
}