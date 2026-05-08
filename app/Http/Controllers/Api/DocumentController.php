<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RecordsAuditLogs;
use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientDocument;
use App\Services\Documents\DocumentAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    use RecordsAuditLogs;

    public function index(Request $request, Patient $patient): JsonResponse
    {
        $this->recordAudit($request, 'viewed_patient_documents', 'patients', $patient->id);

        return response()->json($patient->documents()->latest()->get());
    }

    public function store(Request $request, Patient $patient): JsonResponse
    {
        $data = $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp'],
            'document_type' => ['required', 'string'],
        ]);

        $file = $data['document'];
        $path = $file->store("patients/{$patient->id}/documents", config('filesystems.default', 's3'));

        $document = PatientDocument::create([
            'clinic_id' => $patient->clinic_id,
            'patient_id' => $patient->id,
            'uploaded_by' => $request->user()->id,
            'document_type' => $data['document_type'],
            'original_name' => $file->getClientOriginalName(),
            'disk' => config('filesystems.default', 's3'),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'status' => 'uploaded',
        ]);

        $this->recordAudit($request, 'uploaded_report', 'patient_documents', $document->id);

        return response()->json($document, 201);
    }

    public function show(Request $request, PatientDocument $document): JsonResponse
    {
        $this->recordAudit($request, 'viewed_document', 'patient_documents', $document->id);

        return response()->json([
            'document' => $document,
            'signed_url' => Storage::disk($document->disk)->temporaryUrl(
                $document->path,
                now()->addMinutes((int) env('SIGNED_URL_TTL_MINUTES', 15))
            ),
        ]);
    }

    public function analyze(Request $request, PatientDocument $document, DocumentAnalysisService $service): JsonResponse
    {
        $doctorId = $request->integer('doctor_id');
        $task = $service->enqueueReportAnalysis($document, $doctorId);

        $this->recordAudit($request, 'analyzed_report_with_ai', 'patient_documents', $document->id, ['task_id' => $task->id]);

        return response()->json([
            'task' => $task,
            'message' => 'Report analysis queued. Doctor review required.',
        ], 202);
    }
}
