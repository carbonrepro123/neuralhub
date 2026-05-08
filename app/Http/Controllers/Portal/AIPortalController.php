<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AIAgentTask;
use App\Models\AIOutput;
use App\Models\Appointment;
use App\Models\Patient;
use App\Services\AI\AIOrchestratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AIPortalController extends Controller
{
    public function index(Request $request): View
    {
        $doctorId = optional($request->user()->doctor)->id;
        $hasAiAgentTasksTable = Schema::hasTable('ai_agent_tasks');
        $hasAiOutputsTable = Schema::hasTable('ai_outputs');

        return view('portal.ai.index', [
            'tasks' => $hasAiAgentTasksTable
                ? AIAgentTask::query()
                    ->when($doctorId, fn ($query) => $query->where('doctor_id', $doctorId))
                    ->latest()
                    ->paginate(15)
                : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
            'outputs' => $hasAiOutputsTable
                ? AIOutput::query()
                    ->when($doctorId, fn ($query) => $query->where('doctor_id', $doctorId))
                    ->latest()
                    ->take(8)
                    ->get()
                : collect(),
            'patients' => Patient::orderBy('name')->get(),
            'appointments' => Appointment::latest('appointment_date')->take(20)->get(),
        ]);
    }

    public function show(AIAgentTask $task): View
    {
        return view('portal.ai.show', [
            'task' => Schema::hasTable('ai_outputs') ? $task->load('outputs') : $task,
        ]);
    }

    public function run(Request $request, AIOrchestratorService $ai): RedirectResponse
    {
        $data = $request->validate([
            'agent_type' => ['required', 'string', 'max:255'],
            'patient_id' => ['nullable', 'integer'],
            'appointment_id' => ['nullable', 'integer'],
            'input_text' => ['required', 'string'],
        ]);

        $task = AIAgentTask::create([
            'clinic_id' => optional($request->user()->doctor)->clinic_id,
            'patient_id' => $data['patient_id'] ?? null,
            'doctor_id' => optional($request->user()->doctor)->id,
            'agent_type' => $data['agent_type'],
            'input_text' => $data['input_text'],
            'input_json' => [
                'appointment_id' => $data['appointment_id'] ?? null,
            ],
            'status' => 'processing',
            'priority' => 'normal',
        ]);

        $ai->dispatchTask($task);

        return redirect()->route('portal.ai.show', $task)->with('status', 'AI task completed. Doctor review required.');
    }
}
