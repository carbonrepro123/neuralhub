<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Api\Concerns\RecordsAuditLogs;
use App\Http\Controllers\Controller;
use App\Models\AIOutput;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\SoapNote;
use App\Models\VideoRoom;
use App\Services\AI\AIOrchestratorService;
use App\Services\Video\VideoProviderManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AppointmentPortalController extends Controller
{
    use RecordsAuditLogs;

    public function index(Request $request): View
    {
        $query = Appointment::with(['doctor.user', 'patient', 'videoRoom'])->orderByDesc('appointment_date');
        $doctorId = optional($request->user()->doctor)->id;

        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }

        return view('portal.appointments.index', [
            'appointments' => $query->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('portal.appointments.create', [
            'patients' => Patient::orderBy('name')->get(),
            'doctors' => Doctor::with('user')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
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

        return redirect()->route('portal.appointments.show', $appointment)->with('status', 'Appointment created successfully.');
    }

    public function show(Request $request, Appointment $appointment): View
    {
        $this->recordAudit($request, 'viewed_appointment', 'appointments', $appointment->id);

        return view('portal.appointments.show', [
            'appointment' => $appointment->load(['doctor.user', 'patient.documents.reportExtractions', 'videoRoom', 'soapNotes']),
        ]);
    }

    public function generateRoom(Request $request, Appointment $appointment, VideoProviderManager $providers): RedirectResponse
    {
        $expiresAt = now()->addHours(4);
        $roomName = 'appointment-' . $appointment->id . '-' . Str::lower(Str::random(5));
        $provider = $providers->current();
        $providerName = $providers->providerName();
        $room = $provider->createRoom([
            'name' => $roomName,
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
            'expires_at' => $expiresAt,
        ]);

        VideoRoom::updateOrCreate(
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

        $appointment->update([
            'status' => 'waiting',
            'daily_room_url' => $room['url'],
        ]);

        $this->recordAudit($request, 'created_video_room', 'appointments', $appointment->id);

        return redirect()->route('portal.appointments.consult', $appointment)->with('status', strtoupper($providerName) . ' room is ready.');
    }

    public function consult(Request $request, Appointment $appointment): View
    {
        $this->recordAudit($request, 'joined_video_call', 'appointments', $appointment->id);

        $appointment->update(['status' => 'in_progress']);

        $patient = $appointment->patient->load(['documents.reportExtractions']);
        $outputs = Schema::hasTable('ai_outputs')
            ? AIOutput::where('patient_id', $patient->id)->latest()->take(8)->get()
            : collect();
        $reportInsights = $patient->documents
            ->flatMap(fn ($document) => $document->reportExtractions)
            ->sortByDesc('created_at')
            ->take(4)
            ->values();
        $assignedAgents = $appointment->doctor?->aiAgents()->orderBy('name')->get() ?? collect();

        return view('portal.appointments.consult', [
            'appointment' => $appointment->load(['doctor.user', 'videoRoom']),
            'patient' => $patient,
            'outputs' => $outputs,
            'reportInsights' => $reportInsights,
            'assignedAgents' => $assignedAgents,
            'latestSoap' => SoapNote::where('appointment_id', $appointment->id)->latest()->first(),
        ]);
    }

    public function generateSoap(Request $request, Appointment $appointment, AIOrchestratorService $ai): RedirectResponse
    {
        $task = \App\Models\AIAgentTask::create([
            'clinic_id' => $appointment->clinic_id,
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'agent_type' => 'scribe_agent',
            'input_text' => $request->input('notes', $appointment->notes ?: 'Generate a SOAP note draft from consultation notes.'),
            'input_json' => [
                'appointment_id' => $appointment->id,
            ],
            'status' => 'processing',
            'priority' => 'normal',
        ]);

        $result = $ai->dispatchTask($task);
        $output = $result['output'];

        SoapNote::create([
            'appointment_id' => $appointment->id,
            'doctor_id' => $appointment->doctor_id,
            'patient_id' => $appointment->patient_id,
            'subjective' => data_get($output->output_json, 'subjective'),
            'objective' => data_get($output->output_json, 'objective'),
            'assessment' => data_get($output->output_json, 'assessment'),
            'plan' => data_get($output->output_json, 'plan'),
            'review_status' => 'draft',
        ]);

        return redirect()->route('portal.appointments.consult', $appointment)->with('status', 'SOAP draft generated. Doctor review required.');
    }

    public function complete(Request $request, Appointment $appointment): RedirectResponse
    {
        $appointment->update(['status' => 'completed']);
        $this->recordAudit($request, 'ended_video_call', 'appointments', $appointment->id);

        return redirect()->route('portal.appointments.show', $appointment)->with('status', 'Appointment marked completed.');
    }
}
