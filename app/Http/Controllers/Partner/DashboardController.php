<?php // app/Http/Controllers/Partner/DashboardController.php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('partner.dashboard');
    }
}