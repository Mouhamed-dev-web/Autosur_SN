<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Le dépanneur arrive directement sur ses demandes SOS
        if ($request->user()->hasRole('depanneur')) {
            return redirect()->route('partner.sos.index');
        }

        return view('partner.dashboard');
    }
}