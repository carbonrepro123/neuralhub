@extends('layouts.portal', ['title' => 'ClinixAI Dashboard'])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ ucfirst(str_replace('_', ' ', $user->role)) }} Dashboard</h1>
            <div class="page-subtitle">Practical operations overview for video care, AI review, patient management, and compliance.</div>
        </div>
        <div class="toolbar">
            <a href="{{ route('portal.appointments.create') }}" class="button">New Appointment</a>
            <a href="{{ route('portal.patients.create') }}" class="button secondary">New Patient</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><div class="stat-label">Today's video calls</div><div class="stat-value">{{ $stats['today_video_calls'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Patients waiting</div><div class="stat-value">{{ $stats['patients_waiting'] }}</div></div>
        <div class="stat-card"><div class="stat-label">AI reviews pending</div><div class="stat-value">{{ $stats['ai_reviews_pending'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Compliance expiring</div><div class="stat-value">{{ $stats['compliance_expiring'] }}</div></div>
    </div>

    <div class="grid-3" style="margin-top:22px;">
        <div class="hero-card">
            <div class="pill success">Video Ready</div>
            <h2 style="margin:14px 0 10px;">Launch a patient consult</h2>
            <div class="muted">Create a video appointment, generate a Jitsi room instantly for demos, and open the consultation workspace with the AI side panel.</div>
            <div class="toolbar" style="margin-top:18px;">
                <a href="{{ route('portal.appointments.create') }}" class="button green">Create Video Appointment</a>
            </div>
        </div>
        <div class="hero-card">
            <div class="pill">Clinic AI Employees</div>
            <h2 style="margin:14px 0 10px;">Assign AI staff to doctors</h2>
            <div class="muted">Receptionist, intake, report reader, scribe, follow-up, and compliance agents can be enabled per doctor and clinic workflow.</div>
            <div class="toolbar" style="margin-top:18px;">
                @if(in_array($user->role, ['super_admin', 'clinic_admin']))
                    <a href="{{ route('portal.operations.index') }}" class="button secondary">Manage AI Employees</a>
                @else
                    <a href="{{ route('portal.ai.index') }}" class="button secondary">Review AI Activity</a>
                @endif
            </div>
        </div>
        <div class="hero-card">
            <div class="pill warn">Compliance Bot</div>
            <h2 style="margin:14px 0 10px;">Track doctor renewals</h2>
            <div class="muted">Each doctor has separate compliance tracking for licenses, credentials, and renewals across the clinic organization.</div>
            <div class="toolbar" style="margin-top:18px;">
                <a href="{{ route('portal.compliance.index') }}" class="button secondary">Open Compliance Bot</a>
            </div>
        </div>
    </div>

    <div class="split" style="margin-top:22px;">
        <div class="card">
            <h2>Recent patients</h2>
            <div class="meta-list" style="margin-top:14px;">
                @forelse($recentPatients as $patient)
                    <a class="meta-item" href="{{ route('portal.patients.show', $patient) }}">
                        <strong>{{ $patient->name }}</strong>
                        <div class="muted" style="margin-top:6px;">{{ $patient->email ?: 'No email' }} · {{ $patient->phone ?: 'No phone' }}</div>
                    </a>
                @empty
                    <div class="meta-item">No recent patients yet.</div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <h2>Recent AI agent activity</h2>
            <div class="meta-list" style="margin-top:14px;">
                @forelse($recentAiTasks as $task)
                    <a class="meta-item" href="{{ route('portal.ai.show', $task) }}">
                        <strong>{{ ucwords(str_replace('_', ' ', $task->agent_type)) }}</strong>
                        <div class="muted" style="margin-top:6px;">Status: {{ $task->status }} · {{ $task->created_at?->diffForHumans() }}</div>
                    </a>
                @empty
                    <div class="meta-item">No AI activity yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid-2" style="margin-top:22px;">
        <div class="card">
            <h2>Assigned AI Employees</h2>
            <div class="meta-list" style="margin-top:14px;">
                @forelse($assignedAgents as $agent)
                    <div class="meta-item">
                        <strong>{{ $agent->name }}</strong>
                        <div class="muted" style="margin-top:6px;">Acts on behalf of the doctor for {{ strtolower(str_replace('_', ' ', $agent->agent_type)) }} workflows.</div>
                    </div>
                @empty
                    <div class="meta-item">No AI employees assigned yet. Clinic admin can assign them from Clinic Operations.</div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <h2>Clinic Feature Access</h2>
            <div class="meta-list" style="margin-top:14px;">
                @forelse($clinicFeatures as $clinic)
                    @foreach($clinic->featureFlags as $feature)
                        <div class="meta-item">
                            <strong>{{ $feature->feature_name }}</strong>
                            <div class="muted" style="margin-top:6px;">{{ $clinic->name }} · {{ $feature->enabled ? 'Enabled' : 'Disabled' }}</div>
                        </div>
                    @endforeach
                @empty
                    <div class="meta-item">No clinic feature flags configured yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid-2" style="margin-top:22px;">
        <div class="table-card">
            <h2 style="margin-top:0;">Upcoming appointments</h2>
            <table>
                <thead>
                <tr>
                    <th>Patient</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse($recentAppointments as $appointment)
                    <tr>
                        <td>
                            <a href="{{ route('portal.appointments.show', $appointment) }}">
                                {{ $user->role === 'patient' ? ($appointment->doctor?->user?->name ?: 'Assigned Doctor') : ($appointment->patient?->name ?: 'Patient') }}
                            </a>
                        </td>
                        <td>{{ optional($appointment->appointment_date)->format('M d, Y') }} {{ $appointment->start_time }}</td>
                        <td><span class="pill {{ in_array($appointment->status, ['waiting','scheduled']) ? 'warn' : 'success' }}">{{ $appointment->status }}</span></td>
                        <td>
                            @if($appointment->appointment_type === 'video' && $appointment->videoRoom?->room_url)
                                @if(in_array($user->role, ['doctor', 'clinic_admin', 'super_admin', 'staff']))
                                    @if($appointment->status === 'in_progress')
                                        <a class="button secondary" href="{{ route('portal.appointments.consult', $appointment) }}">Doctor Console</a>
                                    @else
                                        <form method="POST" action="{{ route('portal.appointments.start', $appointment) }}">
                                            @csrf
                                            <button type="submit" class="button secondary">Start Meeting</button>
                                        </form>
                                    @endif
                                @else
                                    <a class="button secondary" href="{{ route('portal.appointments.join', $appointment) }}">Join Call</a>
                                @endif
                            @else
                                <a href="{{ route('portal.appointments.show', $appointment) }}">View</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">No appointments yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-card">
            <h2 style="margin-top:0;">Expiring compliance items</h2>
            <table>
                <thead>
                <tr>
                    <th>Type</th>
                    <th>Expiry</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @forelse($expiringCompliance as $item)
                    <tr>
                        <td><a href="{{ route('portal.compliance.show', $item) }}">{{ $item->certification_type }}</a></td>
                        <td>{{ optional($item->expiry_date)->format('M d, Y') ?: 'N/A' }}</td>
                        <td><span class="pill danger">{{ $item->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3">No expiring items right now.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
