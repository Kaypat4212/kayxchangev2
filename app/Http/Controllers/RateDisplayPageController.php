<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RateDisplayPageController extends Controller
{
    public function index()
    {
        return view('rates.index');
    }

    public function loading()
    {
        return view('rates.loading');
    }

    public function crypto()
    {
        $cryptoRates = DB::table('crypto_rates')->get();
        return view('rates.crypto', ['rates' => $cryptoRates]);
    }
}
