<?php

namespace App\Http\Controllers;

use App\Mail\NewsletterWelcomeMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
            $this->sendWelcomeMail($existing->email, $existing->name ?? '');
            return response()->json(['success' => true, 'message' => 'Welcome back! You have been re-subscribed.']);
        }

        $subscriber = NewsletterSubscriber::create([
            'email'         => $data['email'],
            'name'          => $data['name'] ?? null,
            'subscribed_at' => now(),
        ]);

        $this->sendWelcomeMail($subscriber->email, $subscriber->name ?? '');

        return response()->json(['success' => true, 'message' => 'Thank you for subscribing! Check your email for a welcome message.']);
    }

    public function unsubscribe(Request $request)
    {
        // Support both GET (link from email) and POST (form)
        $email = $request->input('email') ?? $request->query('email');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid email.'], 422);
            }
            return redirect('/')->with('error', 'Invalid unsubscribe link.');
        }

        $sub = NewsletterSubscriber::where('email', $email)->first();
        if ($sub && $sub->is_active) {
            $sub->update(['is_active' => false, 'unsubscribed_at' => now()]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'You have been unsubscribed.']);
        }

        return redirect('/')->with('success', 'You have been unsubscribed from our newsletter.');
    }

    private function sendWelcomeMail(string $email, string $name): void
    {
        try {
            Mail::to($email)->send(new NewsletterWelcomeMail($name, $email));
        } catch (\Throwable $e) {
            Log::warning("Newsletter welcome email failed for {$email}: " . $e->getMessage());
        }
    }
}
