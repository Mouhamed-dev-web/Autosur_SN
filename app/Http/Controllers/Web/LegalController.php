<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class LegalController extends Controller
{
    public function notice()
    {
        return view('legal.notice');
    }

    public function terms()
    {
        return view('legal.terms');
    }

    public function privacy()
    {
        return view('legal.privacy');
    }
}