<?php

namespace App\Jobs;

use App\Models\AIAgentTask;
use App\Services\AI\AIOrchestratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessReportAnalysisJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $taskId)
    {
    }

    public function handle(AIOrchestratorService $orchestrator): void
    {
        $task = AIAgentTask::findOrFail($this->taskId);
        $task->update(['status' => 'processing']);

        $orchestrator->dispatchTask($task);
    }
}
