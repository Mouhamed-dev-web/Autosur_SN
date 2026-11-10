<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return match (true) {
            $user->hasRole('depanneur') => redirect()->route('partner.sos.index'),
            $user->hasRole('garage') => redirect()->route('partner.visits.index'),
            $user->hasRole('assurance') => redirect()->route('partner.insurance.index'),
            default => view('partner.dashboard'),
        };
    }
}