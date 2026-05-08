<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RecordsAuditLogs;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateSoapNoteJob;
use App\Models\AIAgentTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AIController extends Controller
{
    use RecordsAuditLogs;

    public function reportSummary(Request $request): JsonResponse
    {
        return $this->queueTask($request, 'report_reader');
    }

    public function extractVitals(Request $request): JsonResponse
    {
        return $this->queueTask($request, 'vitals_agent');
    }

    public function suggestQuestions(Request $request): JsonResponse
    {
        return $this->queueTask($request, 'follow_up_agent');
    }

    public function generateSoapNote(Request $request): JsonResponse
    {
        $task = $this->createTask($request, 'scribe_agent');
        GenerateSoapNoteJob::dispatch($task->id);

        return response()->json([
            'task' => $task,
            'message' => 'SOAP draft queued. Doctor review required.',
        ], 202);
    }

    public function showTask(Request $request, AIAgentTask $task): JsonResponse
    {
        $this->recordAudit($request, 'viewed_ai_task', 'ai_agent_tasks', $task->id);

        return response()->json($task->load('outputs'));
    }

    private function queueTask(Request $request, string $agentType): JsonResponse
    {
        $task = $this->createTask($request, $agentType);

        $this->recordAudit($request, 'queued_ai_task', 'ai_agent_tasks', $task->id, ['agent_type' => $agentType]);

        return response()->json([
            'task' => $task,
            'message' => 'AI task queued. Doctor review required.',
        ], 202);
    }

    private function createTask(Request $request, string $agentType): AIAgentTask
    {
        $data = $request->validate([
            'clinic_id' => ['nullable', 'integer'],
            'patient_id' => ['nullable', 'integer'],
            'doctor_id' => ['nullable', 'integer'],
            'input_text' => ['nullable', 'string'],
            'input_json' => ['nullable', 'array'],
        ]);

        return AIAgentTask::create([
            ...$data,
            'agent_type' => $agentType,
            'status' => 'queued',
            'priority' => 'normal',
        ]);
    }
}
