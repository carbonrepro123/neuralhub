<?php

namespace App\Services\AI;

use App\Models\AIOutput;
use App\Models\AIAgentTask;
use Illuminate\Support\Facades\Http;

class AIOrchestratorService
{
    public function dispatchTask(AIAgentTask $task): array
    {
        $response = $this->callModel($task);

        $output = AIOutput::create([
            'task_id' => $task->id,
            'patient_id' => $task->patient_id,
            'doctor_id' => $task->doctor_id,
            'agent_type' => $task->agent_type,
            'input_text' => $task->input_text,
            'output_json' => $response,
            'status' => 'completed',
            'reviewed_by_doctor' => false,
            'confidence_score' => $response['confidence_score'] ?? 0.70,
            'disclaimer' => 'AI assistance only. Doctor review required.',
            'doctor_review_status' => 'pending',
        ]);

        $task->update(['status' => 'completed']);

        return [
            'task' => $task->fresh(),
            'output' => $output,
        ];
    }

    private function callModel(AIAgentTask $task): array
    {
        if (! config('services.openai.api_key')) {
            return $this->mockOutput($task);
        }

        $prompt = $this->buildPrompt($task);

        $response = Http::withToken(config('services.openai.api_key'))
            ->post(rtrim(config('services.openai.base_url'), '/') . '/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a healthcare administrative AI assistant. Never make final diagnoses. Always return concise JSON-friendly assistance with doctor review required.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.2,
            ])->json();

        return [
            'agent_type' => $task->agent_type,
            'confidence_score' => 0.78,
            'doctor_review_status' => 'pending',
            'disclaimer' => 'AI assistance only. Doctor review required.',
            'raw_response' => $response,
        ];
    }

    private function buildPrompt(AIAgentTask $task): string
    {
        return json_encode([
            'agent_type' => $task->agent_type,
            'task_id' => $task->id,
            'patient_id' => $task->patient_id,
            'doctor_id' => $task->doctor_id,
            'input_text' => $task->input_text,
            'input_json' => $task->input_json,
            'requirements' => [
                'doctor_review_required' => true,
                'no_final_diagnosis' => true,
                'highlight_abnormal_values' => true,
            ],
        ], JSON_PRETTY_PRINT);
    }

    private function mockOutput(AIAgentTask $task): array
    {
        return match ($task->agent_type) {
            'report_reader' => [
                'summary' => 'Uploaded report reviewed for physician follow-up.',
                'vitals' => [['name' => 'HbA1c', 'value' => '7.4%', 'flag' => 'high']],
                'abnormal_values' => ['HbA1c above target range'],
                'suggested_questions' => ['Ask about medication adherence and diet changes'],
                'confidence_score' => 0.74,
            ],
            'vitals_agent' => [
                'vitals' => [
                    ['name' => 'BP', 'value' => '138/88', 'flag' => 'elevated'],
                    ['name' => 'Heart Rate', 'value' => '84', 'flag' => 'normal'],
                ],
                'confidence_score' => 0.79,
            ],
            'scribe_agent' => [
                'subjective' => 'Patient reports fatigue and intermittent headaches.',
                'objective' => 'Video consultation completed with report review.',
                'assessment' => 'Needs physician correlation with labs and symptoms.',
                'plan' => 'Finalize note after doctor approval and schedule follow-up if needed.',
                'confidence_score' => 0.71,
            ],
            'compliance_agent' => [
                'doctor_name' => 'Sample Doctor',
                'license_number' => 'MD-12345',
                'issue_date' => '2025-01-01',
                'expiry_date' => '2027-01-01',
                'issuing_authority' => 'Texas Medical Board',
                'renewal_checklist' => [
                    'Confirm document readability',
                    'Verify renewal window with issuing authority',
                    'Upload renewed document for approval',
                ],
                'confidence_score' => 0.76,
            ],
            default => [
                'message' => 'Task completed in MVP mock mode.',
                'confidence_score' => 0.70,
            ],
        };
    }
}
