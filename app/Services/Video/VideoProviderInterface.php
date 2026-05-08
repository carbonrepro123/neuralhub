<?php

namespace App\Services\Video;

interface VideoProviderInterface
{
    public function createRoom(array $payload): array;

    public function createToken(string $roomName, array $payload): array;

    public function getRoom(string $roomName): array;

    public function deleteRoom(string $roomName): bool;

    public function getRecording(string $roomName): array;

    public function webhookHandler(array $payload): void;
}
