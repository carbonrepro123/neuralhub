@extends('layouts.portal', ['title' => 'Appointment'])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Appointment #{{ $appointment->id }}</h1>
            <div class="page-subtitle">{{ $appointment->patient?->name }} with {{ $appointment->doctor?->user?->name }} · {{ optional($appointment->appointment_date)->format('M d, Y') }}</div>
        </div>
        <div class="toolbar">
            @if(!$appointment->videoRoom && $appointment->appointment_type === 'video')
                <form method="POST" action="{{ route('portal.appointments.room', $appointment) }}">
                    @csrf
                    <button type="submit">Create Video Room</button>
                </form>
            @endif
            @if($appointment->videoRoom)
                <a href="{{ route('portal.appointments.consult', $appointment) }}" class="button green">Open Consult View</a>
            @endif
        </div>
    </div>

    <div class="split">
        <div class="card">
            <h2>Appointment Details</h2>
            <div class="meta-list" style="margin-top:14px;">
                <div class="meta-item"><strong>Status:</strong> <span class="muted">{{ $appointment->status }}</span></div>
                <div class="meta-item"><strong>Type:</strong> <span class="muted">{{ ucfirst($appointment->appointment_type) }}</span></div>
                <div class="meta-item"><strong>Reason:</strong> <span class="muted">{{ $appointment->reason_for_visit ?: 'N/A' }}</span></div>
                <div class="meta-item"><strong>Doctor:</strong> <span class="muted">{{ $appointment->doctor?->user?->name }}</span></div>
                <div class="meta-item"><strong>Patient:</strong> <span class="muted">{{ $appointment->patient?->name }}</span></div>
            </div>
        </div>
        <div class="card">
            <h2>Video Room</h2>
            @if($appointment->videoRoom)
                <div class="meta-list" style="margin-top:14px;">
                    <div class="meta-item"><strong>Provider:</strong> <span class="muted">{{ $appointment->videoRoom->provider }}</span></div>
                    <div class="meta-item"><strong>Room URL:</strong> <span class="muted">{{ $appointment->videoRoom->room_url }}</span></div>
                    <div class="meta-item"><strong>Expiry:</strong> <span class="muted">{{ optional($appointment->videoRoom->expires_at)->format('M d, Y H:i') }}</span></div>
                </div>
            @else
                <div class="meta-item" style="margin-top:14px;">No room created yet.</div>
            @endif
        </div>
    </div>

    <div class="table-card" style="margin-top:22px;">
        <h2 style="margin-top:0;">SOAP Drafts</h2>
        <table>
            <thead><tr><th>Review Status</th><th>Approved At</th><th>Preview</th></tr></thead>
            <tbody>
            @forelse($appointment->soapNotes as $note)
                <tr>
                    <td>{{ $note->review_status }}</td>
                    <td>{{ optional($note->approved_at)->format('M d, Y H:i') ?: 'Pending' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($note->assessment, 90) }}</td>
                </tr>
            @empty
                <tr><td colspan="3">No SOAP drafts yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
