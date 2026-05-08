<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RecordsAuditLogs;
use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    use RecordsAuditLogs;

    public function index(Request $request): JsonResponse
    {
        $query = Patient::query()->with(['doctor.user', 'documents', 'appointments']);

        if ($search = $request->string('search')->toString()) {
            $query->where('name', 'like', "%{$search}%");
        }

        $this->recordAudit($request, 'viewed_patient_list', 'patients');

        return response()->json($query->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'clinic_id' => ['required', 'integer'],
            'primary_doctor_id' => ['nullable', 'integer'],
            'name' => ['required', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string'],
            'insurance_provider' => ['nullable', 'string'],
            'insurance_member_id' => ['nullable', 'string'],
            'allergies' => ['nullable', 'array'],
            'medications' => ['nullable', 'array'],
            'conditions' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
            'consent_status' => ['nullable', 'string'],
        ]);

        $patient = Patient::create($data);

        $this->recordAudit($request, 'created_patient', 'patients', $patient->id);

        return response()->json($patient, 201);
    }

    public function show(Request $request, Patient $patient): JsonResponse
    {
        $this->recordAudit($request, 'viewed_patient_profile', 'patients', $patient->id);

        return response()->json($patient->load([
            'doctor.user',
            'documents.reportExtractions',
            'appointments.videoRoom',
        ]));
    }

    public function update(Request $request, Patient $patient): JsonResponse
    {
        $patient->update($request->all());

        $this->recordAudit($request, 'updated_patient', 'patients', $patient->id);

        return response()->json($patient->fresh());
    }

    public function destroy(Request $request, Patient $patient): JsonResponse
    {
        $patient->delete();

        $this->recordAudit($request, 'deleted_patient', 'patients', $patient->id);

        return response()->json(status: 204);
    }
}
