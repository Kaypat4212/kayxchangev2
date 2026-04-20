<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:255',
            'name'  => 'nullable|string|max:120',
        ]);

        $existing = NewsletterSubscriber::where('email', $data['email'])->first();

        if ($existing) {
            if ($existing->is_active) {
                return response()->json(['success' => false, 'message' => 'You are already subscribed!']);
            }
            // Re-subscribe
            $existing->update(['is_active' => true, 'subscribed_at' => now(), 'unsubscribed_at' => null]);
            return response()->json(['success' => true, 'message' => 'Welcome back! You have been re-subscribed.']);
        }

        NewsletterSubscriber::create([
            'email'         => $data['email'],
            'name'          => $data['name'] ?? null,
            'subscribed_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Thank you for subscribing!']);
    }

    public function unsubscribe(Request $request)
    {
        $data = $request->validate(['email' => 'required|email']);

        $sub = NewsletterSubscriber::where('email', $data['email'])->first();
        if ($sub && $sub->is_active) {
            $sub->update(['is_active' => false, 'unsubscribed_at' => now()]);
        }

        return response()->json(['success' => true, 'message' => 'You have been unsubscribed.']);
    }
}
