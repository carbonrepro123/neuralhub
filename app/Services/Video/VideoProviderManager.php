<?php

namespace App\Services\Video;

class VideoProviderManager
{
    public function __construct(
        private readonly DailyVideoProvider $daily,
        private readonly JitsiVideoProvider $jitsi,
    ) {
    }

    public function current(): VideoProviderInterface
    {
        if (config('services.daily.key')) {
            return $this->daily;
        }

        return $this->jitsi;
    }

    public function providerName(): string
    {
        return config('services.daily.key') ? 'daily' : 'jitsi';
    }
}
