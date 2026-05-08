<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Api\Concerns\RecordsAuditLogs;
use App\Http\Controllers\Controller;
use App\Models\AIOutput;
use App\Models\AIAgentTask;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientDocument;
use App\Services\AI\AIOrchestratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PatientPortalController extends Controller
{
    use RecordsAuditLogs;

    public function index(Request $request): View
    {
        $query = Patient::with(['doctor.user', 'appointments', 'documents']);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($inner) use ($search): void {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $doctorId = optional($request->user()->doctor)->id;
        if ($doctorId) {
            $query->where('primary_doctor_id', $doctorId);
        }

        $this->recordAudit($request, 'viewed_patient_list', 'patients');

        return view('portal.patients.index', [
            'patients' => $query->latest()->paginate(12)->withQueryString(),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function create(): View
    {
        return view('portal.patients.create', [
            'doctors' => Doctor::with('user')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'clinic_id' => ['required', 'integer'],
            'primary_doctor_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'insurance_provider' => ['nullable', 'string', 'max:255'],
            'insurance_member_id' => ['nullable', 'string', 'max:255'],
            'medical_history' => ['nullable', 'string'],
            'allergies' => ['nullable', 'string'],
            'medications' => ['nullable', 'string'],
            'conditions' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'consent_status' => ['nullable', 'string', 'max:50'],
        ]);

        $patient = Patient::create([
            ...$data,
            'medical_history' => $this->splitTextarea($data['medical_history'] ?? null),
            'allergies' => $this->splitTextarea($data['allergies'] ?? null),
            'medications' => $this->splitTextarea($data['medications'] ?? null),
            'conditions' => $this->splitTextarea($data['conditions'] ?? null),
        ]);

        $this->recordAudit($request, 'created_patient', 'patients', $patient->id);

        return redirect()->route('portal.patients.show', $patient)->with('status', 'Patient created successfully.');
    }

    public function show(Request $request, Patient $patient): View
    {
        $this->recordAudit($request, 'viewed_patient_profile', 'patients', $patient->id);

        return view('portal.patients.show', [
            'patient' => $patient->load([
                'doctor.user',
                'appointments.videoRoom',
                'documents.reportExtractions',
            ]),
            'aiOutputs' => Schema::hasTable('ai_outputs')
                ? AIOutput::where('patient_id', $patient->id)->latest()->take(10)->get()
                : collect(),
        ]);
    }

    public function edit(Patient $patient): View
    {
        return view('portal.patients.edit', [
            'patient' => $patient,
            'doctors' => Doctor::with('user')->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $data = $request->validate([
            'clinic_id' => ['required', 'integer'],
            'primary_doctor_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'insurance_provider' => ['nullable', 'string', 'max:255'],
            'insurance_member_id' => ['nullable', 'string', 'max:255'],
            'medical_history' => ['nullable', 'string'],
            'allergies' => ['nullable', 'string'],
            'medications' => ['nullable', 'string'],
            'conditions' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'consent_status' => ['nullable', 'string', 'max:50'],
        ]);

        $patient->update([
            ...$data,
            'medical_history' => $this->splitTextarea($data['medical_history'] ?? null),
            'allergies' => $this->splitTextarea($data['allergies'] ?? null),
            'medications' => $this->splitTextarea($data['medications'] ?? null),
            'conditions' => $this->splitTextarea($data['conditions'] ?? null),
        ]);

        $this->recordAudit($request, 'updated_patient', 'patients', $patient->id);

        return redirect()->route('portal.patients.show', $patient)->with('status', 'Patient updated successfully.');
    }

    public function uploadDocument(Request $request, Patient $patient): RedirectResponse
    {
        $data = $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp'],
            'document_type' => ['required', 'string', 'max:255'],
        ]);

        $file = $data['document'];
        $path = $file->store("patients/{$patient->id}/documents", config('filesystems.default', 'local'));

        $document = PatientDocument::create([
            'clinic_id' => $patient->clinic_id,
            'patient_id' => $patient->id,
            'uploaded_by' => $request->user()->id,
            'document_type' => $data['document_type'],
            'original_name' => $file->getClientOriginalName(),
            'disk' => config('filesystems.default', 'local'),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'status' => 'uploaded',
        ]);

        $this->recordAudit($request, 'uploaded_report', 'patient_documents', $document->id);

        return redirect()->route('portal.patients.show', $patient)->with('status', 'Document uploaded securely.');
    }

    public function analyzeDocument(Request $request, Patient $patient, PatientDocument $document, AIOrchestratorService $ai): RedirectResponse
    {
        $doctorId = optional($request->user()->doctor)->id;
        if (! $doctorId) {
            return back()->with('error', 'Only a doctor can trigger AI report analysis.');
        }

        $task = AIAgentTask::create([
            'clinic_id' => $document->clinic_id,
            'patient_id' => $document->patient_id,
            'doctor_id' => $doctorId,
            'agent_type' => 'report_reader',
            'input_text' => 'Analyze uploaded report and extract a doctor-review summary with vitals and abnormal values.',
            'input_json' => [
                'document_id' => $document->id,
                'document_type' => $document->document_type,
            ],
            'status' => 'processing',
            'priority' => 'normal',
        ]);

        $ai->dispatchTask($task);
        $this->recordAudit($request, 'analyzed_report_with_ai', 'patient_documents', $document->id, ['task_id' => $task->id]);

        return redirect()->route('portal.ai.show', $task)->with('status', 'AI report analysis completed. Doctor review required.');
    }

    public function downloadDocument(Request $request, Patient $patient, PatientDocument $document)
    {
        $this->recordAudit($request, 'viewed_document', 'patient_documents', $document->id);

        return Storage::disk($document->disk)->download($document->path, $document->original_name);
    }

    private function splitTextarea(?string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value))
            ->filter()
            ->values()
            ->all();
    }
}
