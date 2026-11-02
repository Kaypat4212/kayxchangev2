<?php

namespace App\Http\Controllers;

use App\Models\MobilePushToken;
use Illuminate\Http\Request;

class MobilePushTokenController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:255', 'regex:/^ExponentPushToken\\[[A-Za-z0-9_\\-]+\\]$/'],
            'platform' => ['nullable', 'string', 'max:50'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        MobilePushToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $validated['platform'] ?? null,
                'device_name' => $validated['device_name'] ?? null,
                'last_seen_at' => now(),
            ]
        );

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string', 'max:255'],
        ]);

        MobilePushToken::where('user_id', $request->user()->id)
            ->where('token', $request->string('token'))
            ->delete();

        return response()->json(['success' => true]);
    }
}
