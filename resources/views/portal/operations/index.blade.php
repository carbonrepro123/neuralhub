@extends('layouts.portal', ['title' => 'Clinic Operations'])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Clinic Operations</h1>
            <div class="page-subtitle">Assign AI employees to doctors and control which clinic features are enabled for each customer account.</div>
        </div>
    </div>

    <div class="grid-2">
        <div class="table-card">
            <h2 style="margin-top:0;">Clinic Feature Access</h2>
            @foreach($clinics as $clinic)
                <div class="card" style="margin-top:18px;">
                    <h3 style="margin:0 0 10px;">{{ $clinic->name }}</h3>
                    <div class="meta-list">
                        @foreach($featureCatalog as $featureKey => $featureName)
                            @php
                                $current = $clinic->featureFlags->firstWhere('feature_key', $featureKey);
                            @endphp
                            <form method="POST" action="{{ route('portal.operations.features.toggle', $clinic) }}" class="meta-item" style="display:flex;justify-content:space-between;align-items:center;gap:14px;">
                                @csrf
                                <input type="hidden" name="feature_key" value="{{ $featureKey }}">
                                <input type="hidden" name="feature_name" value="{{ $featureName }}">
                                <input type="hidden" name="enabled" value="{{ $current?->enabled ? 0 : 1 }}">
                                <div>
                                    <strong>{{ $featureName }}</strong>
                                    <div class="muted" style="margin-top:6px;">{{ $current?->enabled ? 'Enabled for this clinic' : 'Currently disabled' }}</div>
                                </div>
                                <button type="submit" class="button secondary">{{ $current?->enabled ? 'Disable' : 'Enable' }}</button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="table-card">
            <h2 style="margin-top:0;">Doctor AI Employees</h2>
            @foreach($doctors as $doctor)
                <div class="card" style="margin-top:18px;">
                    <div style="display:flex;justify-content:space-between;gap:14px;align-items:flex-start;">
                        <div>
                            <h3 style="margin:0 0 8px;">{{ $doctor->user?->name }}</h3>
                            <div class="muted">{{ $doctor->clinic?->name }} · {{ $doctor->specialty ?: 'General Practice' }}</div>
                        </div>
                        <div class="pill">{{ $doctor->aiAgents->count() }} assigned</div>
                    </div>

                    <div class="meta-list" style="margin-top:14px;">
                        @forelse($doctor->aiAgents as $agent)
                            <div class="meta-item">
                                <strong>{{ $agent->name }}</strong>
                                <div class="muted" style="margin-top:6px;">Acts as a clinic-side AI employee for {{ strtolower($doctor->user?->name ?? 'this doctor') }}.</div>
                            </div>
                        @empty
                            <div class="meta-item">No AI employees assigned yet.</div>
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route('portal.operations.agents.assign', $doctor) }}" style="margin-top:18px;">
                        @csrf
                        <div class="field">
                            <label>Assign AI Employee</label>
                            <select name="ai_agent_id">
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="toolbar" style="margin-top:14px;">
                            <button type="submit">Assign to Doctor</button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
@endsection
