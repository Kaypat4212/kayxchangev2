<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CryptoController extends Controller
{
    public function dashboard()
    {
        return view('dashboard');
    }

    public function buy()
    {
        return view('buy');
    }

    public function sell()
    {
        return view('sell');
    }

   
}
