<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RecordsAuditLogs;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\Video\DailyVideoProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    use RecordsAuditLogs;

    public function index(Request $request): JsonResponse
    {
        $appointments = Appointment::with(['doctor.user', 'patient', 'videoRoom'])
            ->orderBy('appointment_date')
            ->paginate();

        return response()->json($appointments);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'clinic_id' => ['required', 'integer'],
            'doctor_id' => ['required', 'integer'],
            'patient_id' => ['required', 'integer'],
            'appointment_type' => ['required', 'in:video,in_person'],
            'appointment_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['nullable'],
            'reason_for_visit' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $appointment = Appointment::create([
            ...$data,
            'status' => 'scheduled',
        ]);

        $this->recordAudit($request, 'created_appointment', 'appointments', $appointment->id);

        return response()->json($appointment, 201);
    }

    public function show(Request $request, Appointment $appointment): JsonResponse
    {
        $this->recordAudit($request, 'viewed_appointment', 'appointments', $appointment->id);

        return response()->json($appointment->load(['doctor.user', 'patient', 'videoRoom', 'soapNotes']));
    }

    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        $appointment->update($request->all());

        $this->recordAudit($request, 'updated_appointment', 'appointments', $appointment->id);

        return response()->json($appointment->fresh());
    }

    public function destroy(Request $request, Appointment $appointment): JsonResponse
    {
        $appointment->delete();

        $this->recordAudit($request, 'deleted_appointment', 'appointments', $appointment->id);

        return response()->json(status: 204);
    }

    public function cancel(Request $request, Appointment $appointment): JsonResponse
    {
        $appointment->update(['status' => 'cancelled']);

        $this->recordAudit($request, 'cancelled_appointment', 'appointments', $appointment->id);

        return response()->json($appointment->fresh());
    }

    public function startCall(Request $request, Appointment $appointment, DailyVideoProvider $provider): JsonResponse
    {
        $room = $provider->createRoom([
            'name' => 'appointment-' . $appointment->id,
            'expires_at' => now()->addHours(4),
        ]);

        $appointment->update([
            'status' => 'in_progress',
            'daily_room_url' => $room['url'] ?? null,
        ]);

        $this->recordAudit($request, 'joined_video_call', 'appointments', $appointment->id);

        return response()->json([
            'appointment' => $appointment->fresh(),
            'video_room' => $room,
        ]);
    }

    public function endCall(Request $request, Appointment $appointment): JsonResponse
    {
        $appointment->update(['status' => 'completed']);

        $this->recordAudit($request, 'ended_video_call', 'appointments', $appointment->id);

        return response()->json($appointment->fresh());
    }
}
