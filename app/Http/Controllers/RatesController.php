<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RatesController extends Controller
{
    public function index()
    {
        return view('rates.index');
    }

    public function cryptoRates()
    {
        return view('rates.crypto');
    }

    public function giftCardRates()
    {
        return view('rates.giftcard');
    }
}
