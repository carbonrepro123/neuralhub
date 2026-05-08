<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RecordsAuditLogs;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessComplianceDocumentJob;
use App\Models\AIAgentTask;
use App\Models\ComplianceItem;
use App\Services\Compliance\ComplianceReminderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplianceController extends Controller
{
    use RecordsAuditLogs;

    public function index(Request $request): JsonResponse
    {
        $items = ComplianceItem::with('reminders')->paginate();
        $this->recordAudit($request, 'viewed_compliance_dashboard', 'compliance_items');

        return response()->json([
            'items' => $items,
            'stats' => [
                'active' => ComplianceItem::where('status', 'valid')->count(),
                'expiring_90' => ComplianceItem::whereDate('expiry_date', '<=', now()->addDays(90))->count(),
                'expiring_60' => ComplianceItem::whereDate('expiry_date', '<=', now()->addDays(60))->count(),
                'expiring_30' => ComplianceItem::whereDate('expiry_date', '<=', now()->addDays(30))->count(),
                'expired' => ComplianceItem::whereDate('expiry_date', '<', now())->count(),
                'missing_documents' => ComplianceItem::whereNull('document_upload')->count(),
            ],
        ]);
    }

    public function store(Request $request, ComplianceReminderService $reminders): JsonResponse
    {
        $data = $request->validate([
            'doctor_id' => ['required', 'integer'],
            'certification_type' => ['required', 'string'],
            'issuing_authority' => ['nullable', 'string'],
            'license_number' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'renewal_frequency' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $item = ComplianceItem::create([
            ...$data,
            'status' => 'pending_review',
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $reminders->seedDefaultReminders($item);

        $this->recordAudit($request, 'created_compliance_item', 'compliance_items', $item->id);

        return response()->json($item->load('reminders'), 201);
    }

    public function show(Request $request, ComplianceItem $complianceItem): JsonResponse
    {
        $this->recordAudit($request, 'viewed_compliance_item', 'compliance_items', $complianceItem->id);

        return response()->json($complianceItem->load('doctor.user', 'reminders'));
    }

    public function update(Request $request, ComplianceItem $complianceItem): JsonResponse
    {
        $complianceItem->update([
            ...$request->all(),
            'updated_by' => $request->user()->id,
        ]);

        $this->recordAudit($request, 'updated_compliance_item', 'compliance_items', $complianceItem->id);

        return response()->json($complianceItem->fresh('reminders'));
    }

    public function uploadDocument(Request $request, ComplianceItem $complianceItem): JsonResponse
    {
        $data = $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $path = $data['document']->store("compliance/{$complianceItem->id}", config('filesystems.default', 's3'));
        $complianceItem->update([
            'document_upload' => $path,
            'status' => 'pending_review',
        ]);

        $this->recordAudit($request, 'uploaded_compliance_document', 'compliance_items', $complianceItem->id);

        return response()->json($complianceItem->fresh());
    }

    public function analyzeDocument(Request $request, ComplianceItem $complianceItem): JsonResponse
    {
        $task = AIAgentTask::create([
            'clinic_id' => $complianceItem->doctor->clinic_id,
            'doctor_id' => $complianceItem->doctor_id,
            'agent_type' => 'compliance_agent',
            'input_text' => 'Extract renewal fields and generate a renewal checklist. Doctor review required.',
            'input_json' => [
                'compliance_item_id' => $complianceItem->id,
                'document_upload' => $complianceItem->document_upload,
            ],
            'status' => 'queued',
            'priority' => 'normal',
        ]);

        ProcessComplianceDocumentJob::dispatch($task->id);

        $this->recordAudit($request, 'analyzed_compliance_document', 'compliance_items', $complianceItem->id, ['task_id' => $task->id]);

        return response()->json([
            'task' => $task,
            'message' => 'Compliance document queued for extraction. Doctor review required.',
        ], 202);
    }

    public function createReminder(Request $request, ComplianceItem $complianceItem): JsonResponse
    {
        $reminder = $complianceItem->reminders()->create($request->validate([
            'channel' => ['required', 'string'],
            'send_at' => ['required', 'date'],
            'payload' => ['nullable', 'array'],
        ]));

        $this->recordAudit($request, 'created_compliance_reminder', 'compliance_reminders', $reminder->id);

        return response()->json($reminder, 201);
    }
}
