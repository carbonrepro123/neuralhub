@extends('layouts.portal', ['title' => 'Join Video Consultation'])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Join Consultation</h1>
            <div class="page-subtitle">Patient meeting room for {{ $appointment->doctor?->user?->name ?: 'assigned doctor' }}.</div>
        </div>
        <div class="toolbar">
            <a class="button secondary" href="{{ route('dashboard') }}">Back to Dashboard</a>
        </div>
    </div>

    @if($appointment->status !== 'in_progress')
        <div class="card">
            <h2>Doctor has not started the meeting yet</h2>
            <div class="muted" style="margin-top:10px;">This is the patient waiting room. Once the doctor starts the consult, refresh this page and the meeting will open here.</div>
        </div>
    @else
        <div class="split">
            <div class="card">
                <h2>Live Meeting</h2>
                @if($appointment->videoRoom?->room_url)
                    <div class="video-frame" style="margin-top:16px;">
                        <iframe src="{{ $appointment->videoRoom->patient_join_url }}" allow="camera; microphone; fullscreen; display-capture" referrerpolicy="origin"></iframe>
                    </div>
                @else
                    <div class="meta-item" style="margin-top:14px;">Meeting link is not ready yet.</div>
                @endif
            </div>

            <div class="card">
                <h2>Visit Summary</h2>
                <div class="meta-list" style="margin-top:14px;">
                    <div class="meta-item"><strong>Doctor</strong><div class="muted" style="margin-top:6px;">{{ $appointment->doctor?->user?->name ?: 'Assigned doctor' }}</div></div>
                    <div class="meta-item"><strong>Date</strong><div class="muted" style="margin-top:6px;">{{ optional($appointment->appointment_date)->format('M d, Y') }} {{ $appointment->start_time }}</div></div>
                    <div class="meta-item"><strong>Reason</strong><div class="muted" style="margin-top:6px;">{{ $appointment->reason_for_visit ?: 'Not provided' }}</div></div>
                    <div class="meta-item"><strong>Status</strong><div class="muted" style="margin-top:6px;">{{ $appointment->status }}</div></div>
                </div>

                <h2 style="margin-top:22px;">Meeting Activity</h2>
                <div class="meta-list" style="margin-top:14px;">
                    @forelse($meetingActivity as $entry)
                        <div class="meta-item">
                            <strong>{{ ucwords(str_replace('_', ' ', $entry->action)) }}</strong>
                            <div class="muted" style="margin-top:6px;">{{ ucfirst($entry->metadata['role'] ?? 'user') }} · {{ $entry->created_at?->format('M d, Y H:i') }}</div>
                        </div>
                    @empty
                        <div class="meta-item">No meeting activity yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
@endsection
