<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Api\Concerns\RecordsAuditLogs;
use App\Http\Controllers\Controller;
use App\Models\AIAgentTask;
use App\Models\ComplianceDocument;
use App\Models\ComplianceItem;
use App\Models\Doctor;
use App\Services\AI\AIOrchestratorService;
use App\Services\Compliance\ComplianceReminderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CompliancePortalController extends Controller
{
    use RecordsAuditLogs;

    public function index(Request $request): View
    {
        $doctorId = optional($request->user()->doctor)->id;
        $query = ComplianceItem::with(['doctor.user', 'reminders'])->orderBy('expiry_date');

        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }

        return view('portal.compliance.index', [
            'items' => $query->paginate(12),
            'stats' => [
                'active' => ComplianceItem::where('status', 'valid')->count(),
                'expiring_90' => ComplianceItem::whereDate('expiry_date', '<=', now()->addDays(90))->count(),
                'expired' => ComplianceItem::whereDate('expiry_date', '<', now())->count(),
                'missing_documents' => ComplianceItem::whereNull('document_upload')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('portal.compliance.create', [
            'doctors' => Doctor::with('user')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request, ComplianceReminderService $reminders): RedirectResponse
    {
        $data = $request->validate([
            'doctor_id' => ['required', 'integer'],
            'certification_type' => ['required', 'string', 'max:255'],
            'issuing_authority' => ['nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'renewal_frequency' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $item = ComplianceItem::create([
            ...$data,
            'status' => 'pending_review',
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        if ($item->expiry_date) {
            $reminders->seedDefaultReminders($item);
        }

        $this->recordAudit($request, 'created_compliance_item', 'compliance_items', $item->id);

        return redirect()->route('portal.compliance.show', $item)->with('status', 'Compliance item added.');
    }

    public function show(Request $request, ComplianceItem $compliance): View
    {
        $this->recordAudit($request, 'viewed_compliance_item', 'compliance_items', $compliance->id);

        return view('portal.compliance.show', [
            'item' => $compliance->load(['doctor.user', 'reminders']),
            'documents' => ComplianceDocument::where('compliance_item_id', $compliance->id)->latest()->get(),
            'tasks' => Schema::hasTable('ai_agent_tasks')
                ? AIAgentTask::where('doctor_id', $compliance->doctor_id)
                    ->where('agent_type', 'compliance_agent')
                    ->latest()
                    ->take(5)
                    ->get()
                : collect(),
        ]);
    }

    public function uploadDocument(Request $request, ComplianceItem $compliance): RedirectResponse
    {
        $data = $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $file = $data['document'];
        $path = $file->store("compliance/{$compliance->id}", config('filesystems.default', 'local'));

        ComplianceDocument::create([
            'compliance_item_id' => $compliance->id,
            'uploaded_by' => $request->user()->id,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => config('filesystems.default', 'local'),
            'status' => 'uploaded',
        ]);

        $compliance->update([
            'document_upload' => $path,
            'status' => 'pending_review',
        ]);

        $this->recordAudit($request, 'compliance_document_uploaded', 'compliance_items', $compliance->id);

        return redirect()->route('portal.compliance.show', $compliance)->with('status', 'Compliance document uploaded.');
    }

    public function analyze(Request $request, ComplianceItem $compliance, AIOrchestratorService $ai): RedirectResponse
    {
        $task = AIAgentTask::create([
            'clinic_id' => $compliance->doctor->clinic_id,
            'doctor_id' => $compliance->doctor_id,
            'agent_type' => 'compliance_agent',
            'input_text' => 'Read compliance document, extract renewal dates, and generate a checklist.',
            'input_json' => [
                'compliance_item_id' => $compliance->id,
                'document_upload' => $compliance->document_upload,
            ],
            'status' => 'processing',
            'priority' => 'normal',
        ]);

        $ai->dispatchTask($task);
        $this->recordAudit($request, 'compliance_item_updated', 'compliance_items', $compliance->id, ['task_id' => $task->id]);

        return redirect()->route('portal.ai.show', $task)->with('status', 'Compliance AI analysis complete. Doctor review required.');
    }
}
