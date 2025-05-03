<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function settings()
    {
        return view('settings.index');
    }

    public function editBank()
    {
        $user = Auth::user();
        return view('settings.edit-bank', compact('user'));
    }

    public function updateBank(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string',
            'account_number' => 'required|numeric',
            'account_name' => 'required|string',
        ]);

        $user = Auth::user();
        $user->bank_name = $request->bank_name;
        $user->account_number = $request->account_number;
        $user->account_name = $request->account_name;
        $user->save();

        return redirect()->route('edit.bank')->with('success', 'Bank details updated successfully.');
    }

    public function changePasswordForm()
    {
        return view('settings.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password changed successfully.');
    }
}
