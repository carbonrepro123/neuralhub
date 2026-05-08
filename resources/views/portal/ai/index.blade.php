@extends('layouts.portal', ['title' => 'AI Activity'])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">AI Agents</h1>
            <div class="page-subtitle">Run AI operational helpers, inspect outputs, and review stored assistance results.</div>
        </div>
    </div>

    <div class="split">
        <div class="card">
            <h2>Run AI Agent</h2>
            <form method="POST" action="{{ route('portal.ai.run') }}" style="margin-top:14px;">
                @csrf
                <div class="form-grid">
                    <div class="field">
                        <label>Agent Type</label>
                        <select name="agent_type">
                            <option value="receptionist_agent">AI Receptionist Agent</option>
                            <option value="intake_agent">AI Intake Agent</option>
                            <option value="report_reader">AI Report Reader Agent</option>
                            <option value="vitals_agent">AI Vitals Agent</option>
                            <option value="abnormal_value_agent">AI Abnormal Value Agent</option>
                            <option value="scribe_agent">AI Scribe Agent</option>
                            <option value="follow_up_agent">AI Follow-up Agent</option>
                            <option value="compliance_agent">AI Compliance Agent</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Patient</label>
                        <select name="patient_id">
                            <option value="">No patient</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field full">
                        <label>Prompt / Input</label>
                        <textarea name="input_text" required>Generate an assistance draft. Doctor review required.</textarea>
                    </div>
                </div>
                <div class="toolbar" style="margin-top:18px;">
                    <button type="submit">Run Agent</button>
                </div>
            </form>
        </div>
        <div class="card">
            <h2>Recent Outputs</h2>
            <div class="meta-list" style="margin-top:14px;">
                @forelse($outputs as $output)
                    <div class="meta-item">
                        <strong>{{ ucwords(str_replace('_', ' ', $output->agent_type)) }}</strong>
                        <div class="muted" style="margin-top:8px;">Confidence {{ $output->confidence_score }} · {{ $output->doctor_review_status }}</div>
                    </div>
                @empty
                    <div class="meta-item">No outputs yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="table-card" style="margin-top:22px;">
        <table>
            <thead>
            <tr>
                <th>Agent</th>
                <th>Status</th>
                <th>Created</th>
                <th>Open</th>
            </tr>
            </thead>
            <tbody>
            @forelse($tasks as $task)
                <tr>
                    <td>{{ ucwords(str_replace('_', ' ', $task->agent_type)) }}</td>
                    <td>{{ $task->status }}</td>
                    <td>{{ $task->created_at?->diffForHumans() }}</td>
                    <td><a href="{{ route('portal.ai.show', $task) }}">View</a></td>
                </tr>
            @empty
                <tr><td colspan="4">No AI tasks yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:18px;">{{ $tasks->links() }}</div>
    </div>
@endsection
