<?php

namespace App\Services\Documents;

use App\Jobs\ProcessReportAnalysisJob;
use App\Models\AIAgentTask;
use App\Models\PatientDocument;

class DocumentAnalysisService
{
    public function enqueueReportAnalysis(PatientDocument $document, int $doctorId): AIAgentTask
    {
        $task = AIAgentTask::create([
            'clinic_id' => $document->clinic_id,
            'patient_id' => $document->patient_id,
            'doctor_id' => $doctorId,
            'agent_type' => 'report_reader',
            'input_text' => 'Analyze uploaded report and extract a doctor-review summary.',
            'input_json' => [
                'document_id' => $document->id,
                'document_type' => $document->document_type,
            ],
            'status' => 'queued',
            'priority' => 'normal',
        ]);

        ProcessReportAnalysisJob::dispatch($task->id);

        return $task;
    }
}
