<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BuyTrade;
use App\Models\SellTrade;


class AdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            if (Auth::user()->is_admin) {
                return redirect()->route('admin.dashboard');
            } else {
                Auth::logout();
                return redirect()->route('admin.login')->withErrors(['email' => 'Unauthorized access.']);
            }
        }

        return redirect()->route('admin.login')->withErrors(['email' => 'Invalid credentials.']);
    }

    public function dashboard()
    {
        $totalUsers = User::count();
        return view('admin.dashboard', compact('totalUsers'));
    }

    // New 'users' method to display all users
    public function users()
    {
        $users = User::all();  // Fetch all users
        return view('admin.users', compact('users'));
    }

    public function showTrades()
    {
        // Paginate the buy trades, fetch 10 per page
        $buyTrades = BuyTrade::latest()->paginate(10);

        // You can also fetch sell trades similarly, if you want to include them
        $sellTrades = SellTrade::latest()->paginate(10);

        return view('admin.trades', compact('buyTrades', 'sellTrades'));
    }

    public function updateBuyStatus(Request $request, $id)
    {
        $trade = BuyTrade::findOrFail($id);
        $trade->update([
            'status' => $request->status
        ]);
        return back()->with('success', 'Buy trade status updated.');
    }

    public function updateSellStatus(Request $request, $id)
    {
        $trade = SellTrade::findOrFail($id);
        $trade->update([
            'status' => $request->status
        ]);
        return back()->with('success', 'Sell trade status updated.');
    }
}
