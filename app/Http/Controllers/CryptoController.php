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

    public function sellCrypto(Request $request)
    {
        // Validate the form fields
        $request->validate([
            'coin' => 'required',
            'amount' => 'required|numeric',
            'proof' => 'required|image|max:2048'
        ]);

        // Get the authenticated user
        $user = auth()->user();

        // Save the uploaded file
        $proofPath = $request->file('proof')->store('proofs', 'public');

        // Create the message
        $message = "🚨 New Crypto Sell Request:\n\n"
            . "👤 Name: {$user->name}\n"
            . "📧 Email: {$user->email}\n"
            . "💰 Coin: {$request->coin}\n"
            . "📦 Amount: {$request->amount}";

        // Send to Telegram
        $botToken = '7573773403:AAE3Bc2DNhI3Y-NwEGJ_Rw22Ct0Ue9jhl9w';
        $chatId = '6063090844';

        file_get_contents("https://api.telegram.org/bot{$botToken}/sendMessage?chat_id={$chatId}&text=" . urlencode($message));

        // Send email with file attachment
        Mail::raw($message, function ($mail) use ($proofPath) {
            $mail->to('your@email.com')
                ->subject('New Crypto Sell Request')
                ->attach(public_path("storage/{$proofPath}"));
        });

        return back()->with('success', 'Your request has been received!');
    }
}
