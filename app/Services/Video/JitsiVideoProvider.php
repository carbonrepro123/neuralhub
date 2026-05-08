<?php

namespace App\Services\Video;

class JitsiVideoProvider implements VideoProviderInterface
{
    public function createRoom(array $payload): array
    {
        $roomName = $payload['name'] ?? 'clinixai-room';
        $baseUrl = rtrim(config('services.jitsi.base_url', 'https://meet.jit.si'), '/');

        return [
            'name' => $roomName,
            'url' => "{$baseUrl}/{$roomName}",
            'provider' => 'jitsi',
        ];
    }

    public function createToken(string $roomName, array $payload): array
    {
        return [
            'token' => null,
            'provider' => 'jitsi',
        ];
    }

    public function getRoom(string $roomName): array
    {
        return $this->createRoom(['name' => $roomName]);
    }

    public function deleteRoom(string $roomName): bool
    {
        return true;
    }

    public function getRecording(string $roomName): array
    {
        return [];
    }

    public function webhookHandler(array $payload): void
    {
    }
}
