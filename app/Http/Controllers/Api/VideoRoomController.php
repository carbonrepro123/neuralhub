<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RecordsAuditLogs;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\VideoRoom;
use App\Services\Video\VideoProviderManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoRoomController extends Controller
{
    use RecordsAuditLogs;

    public function store(Request $request, VideoProviderManager $providers): JsonResponse
    {
        $data = $request->validate([
            'appointment_id' => ['required', 'integer'],
        ]);

        $appointment = Appointment::findOrFail($data['appointment_id']);
        $expiresAt = now()->addHours(4);

        $provider = $providers->current();
        $providerName = $providers->providerName();

        $room = $provider->createRoom([
            'name' => 'appointment-' . $appointment->id,
            'expires_at' => $expiresAt,
        ]);

        $doctorToken = $provider->createToken($room['name'], [
            'user_name' => 'Doctor',
            'is_owner' => true,
            'enable_screenshare' => true,
            'expires_at' => $expiresAt,
        ]);

        $patientToken = $provider->createToken($room['name'], [
            'user_name' => 'Patient',
            'is_owner' => false,
            'enable_screenshare' => false,
            'expires_at' => $expiresAt,
        ]);

        $videoRoom = VideoRoom::updateOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'provider' => $providerName,
                'external_room_id' => $room['name'],
                'room_url' => $room['url'],
                'doctor_token' => $doctorToken['token'] ?? null,
                'patient_token' => $patientToken['token'] ?? null,
                'expires_at' => $expiresAt,
                'metadata' => $room,
            ]
        );

        $this->recordAudit($request, 'created_video_room', 'video_rooms', $videoRoom->id);

        return response()->json($videoRoom, 201);
    }

    public function token(Request $request, VideoRoom $videoRoom, VideoProviderManager $providers): JsonResponse
    {
        $role = $request->string('role')->toString() ?: 'patient';
        $provider = $providers->current();

        $token = $provider->createToken($videoRoom->external_room_id, [
            'user_name' => ucfirst($role),
            'is_owner' => $role === 'doctor',
            'enable_screenshare' => $role === 'doctor',
            'expires_at' => $videoRoom->expires_at ?? now()->addHour(),
        ]);

        $this->recordAudit($request, 'generated_video_token', 'video_rooms', $videoRoom->id, ['role' => $role]);

        return response()->json($token);
    }

    public function show(Request $request, VideoRoom $videoRoom): JsonResponse
    {
        $this->recordAudit($request, 'viewed_video_room', 'video_rooms', $videoRoom->id);

        return response()->json($videoRoom);
    }
}
