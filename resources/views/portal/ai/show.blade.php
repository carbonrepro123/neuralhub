@extends('layouts.portal', ['title' => 'AI Task'])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">AI Task #{{ $task->id }}</h1>
            <div class="page-subtitle">Doctor review required for all AI outputs.</div>
        </div>
    </div>

    <div class="grid-2">
        <div class="card">
            <h2>Task Metadata</h2>
            <div class="meta-list" style="margin-top:14px;">
                <div class="meta-item"><strong>Agent Type:</strong> <span class="muted">{{ $task->agent_type }}</span></div>
                <div class="meta-item"><strong>Status:</strong> <span class="muted">{{ $task->status }}</span></div>
                <div class="meta-item"><strong>Patient ID:</strong> <span class="muted">{{ $task->patient_id ?: 'N/A' }}</span></div>
                <div class="meta-item"><strong>Doctor ID:</strong> <span class="muted">{{ $task->doctor_id ?: 'N/A' }}</span></div>
                <div class="meta-item"><strong>Input:</strong><div class="muted" style="margin-top:8px;">{{ $task->input_text }}</div></div>
            </div>
        </div>
        <div class="card">
            <h2>Output</h2>
            @forelse($task->outputs as $output)
                <div class="meta-item" style="margin-top:14px;">
                    <strong>{{ $output->disclaimer }}</strong>
                    <pre style="white-space: pre-wrap; margin-top: 10px; color:#475569;">{{ json_encode($output->output_json, JSON_PRETTY_PRINT) }}</pre>
                </div>
            @empty
                <div class="meta-item" style="margin-top:14px;">No output has been stored yet.</div>
            @endforelse
        </div>
    </div>
@endsection
