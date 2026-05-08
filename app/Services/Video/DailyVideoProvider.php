<?php

namespace App\Services\Video;

use Illuminate\Support\Facades\Http;

class DailyVideoProvider implements VideoProviderInterface
{
    public function createRoom(array $payload): array
    {
        return Http::withToken(config('services.daily.key'))
            ->post(config('services.daily.base_url') . '/rooms', [
                'name' => $payload['name'],
                'privacy' => 'private',
                'properties' => [
                    'enable_chat' => true,
                    'enable_screenshare' => true,
                    'enable_prejoin_ui' => true,
                    'exp' => $payload['expires_at']->timestamp,
                    'start_video_off' => false,
                    'start_audio_off' => false,
                ],
            ])->json();
    }

    public function createToken(string $roomName, array $payload): array
    {
        return Http::withToken(config('services.daily.key'))
            ->post(config('services.daily.base_url') . '/meeting-tokens', [
                'properties' => [
                    'room_name' => $roomName,
                    'user_name' => $payload['user_name'],
                    'is_owner' => $payload['is_owner'] ?? false,
                    'enable_screenshare' => $payload['enable_screenshare'] ?? false,
                    'exp' => $payload['expires_at']->timestamp,
                ],
            ])->json();
    }

    public function getRoom(string $roomName): array
    {
        return Http::withToken(config('services.daily.key'))
            ->get(config('services.daily.base_url') . '/rooms/' . $roomName)
            ->json();
    }

    public function deleteRoom(string $roomName): bool
    {
        return Http::withToken(config('services.daily.key'))
            ->delete(config('services.daily.base_url') . '/rooms/' . $roomName)
            ->successful();
    }

    public function getRecording(string $roomName): array
    {
        return Http::withToken(config('services.daily.key'))
            ->get(config('services.daily.base_url') . '/recordings', ['room_name' => $roomName])
            ->json();
    }

    public function webhookHandler(array $payload): void
    {
        // Record webhook events in audit logs or appointment activity streams.
    }
}
