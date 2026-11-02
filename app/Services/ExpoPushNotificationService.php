<?php

namespace App\Services;

use App\Models\MobilePushToken;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoPushNotificationService
{
    private const ENDPOINT = 'https://exp.host/--/api/v2/push/send';

    public function sendToUser(User $user, string $title, string $body, array $data = []): int
    {
        return $this->sendToTokens(
            $user->mobilePushTokens()->pluck('token'),
            $title,
            $body,
            $data
        );
    }

    public function sendBroadcast(string $title, string $body, array $data = []): int
    {
        return $this->sendToTokens(
            MobilePushToken::query()->pluck('token'),
            $title,
            $body,
            $data
        );
    }

    /**
     * @param  \Illuminate\Support\Collection<int, string>|array<int, string>  $tokens
     */
    public function sendToTokens(Collection|array $tokens, string $title, string $body, array $data = []): int
    {
        $tokens = collect($tokens)
            ->filter(fn ($token) => is_string($token) && str_starts_with($token, 'ExponentPushToken['))
            ->unique()
            ->values();

        if ($tokens->isEmpty()) {
            return 0;
        }

        $sent = 0;

        foreach ($tokens->chunk(100) as $chunk) {
            $messages = $chunk->map(fn ($token) => [
                'to' => $token,
                'sound' => 'default',
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ])->values()->all();

            try {
                $response = Http::timeout(10)->post(self::ENDPOINT, $messages);

                if (!$response->successful()) {
                    Log::warning('Expo push notification request failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    continue;
                }

                $sent += count($messages);
            } catch (\Throwable $e) {
                Log::warning('Expo push notification exception', [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }
}
