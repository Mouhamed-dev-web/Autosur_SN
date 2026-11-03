<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $vehicles = $request->user()->vehicles()->latest()->get();

        return view('client.dashboard', compact('vehicles'));
    }
}