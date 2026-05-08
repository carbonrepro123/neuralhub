@extends('layouts.portal', ['title' => 'Consultation'])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Live Consultation Assistant</h1>
            <div class="page-subtitle">AI assistance only. Doctor review required.</div>
        </div>
        <div class="toolbar">
            @if($appointment->videoRoom?->room_url)
                <a class="button green" href="{{ $appointment->videoRoom->room_url }}" target="_blank">Join {{ strtoupper($appointment->videoRoom->provider ?? 'video') }} Room</a>
            @endif
            <form method="POST" action="{{ route('portal.appointments.complete', $appointment) }}">
                @csrf
                <button class="button secondary" type="submit">Mark Completed</button>
            </form>
        </div>
    </div>

    <div class="split">
        <div class="card">
            <h2>Video Consultation</h2>
            <div class="meta-item" style="margin-top:14px;">
                <strong>{{ strtoupper($appointment->videoRoom?->provider ?? 'video') }} Room</strong>
                <div class="muted" style="margin-top:8px;">{{ $appointment->videoRoom?->room_url ?: 'Generate the room first.' }}</div>
            </div>
            <div class="meta-item" style="margin-top:14px;">
                <strong>Internal Visit Notes</strong>
                <div class="muted" style="margin-top:8px;">{{ $appointment->notes ?: 'No note yet.' }}</div>
            </div>
            <form method="POST" action="{{ route('portal.appointments.soap', $appointment) }}" style="margin-top:18px;">
                @csrf
                <div class="field">
                    <label>Consultation Notes for AI Scribe</label>
                    <textarea name="notes">{{ $appointment->notes }}</textarea>
                </div>
                <div class="toolbar" style="margin-top:18px;">
                    <button type="submit">Generate SOAP Draft</button>
                </div>
            </form>
        </div>
        <div class="card">
            <h2>Patient Overview</h2>
            <div class="meta-list" style="margin-top:14px;">
                <div class="meta-item"><strong>Name:</strong> <span class="muted">{{ $patient->name }}</span></div>
                <div class="meta-item"><strong>Allergies:</strong> <span class="muted">{{ implode(', ', $patient->allergies ?? []) ?: 'None listed' }}</span></div>
                <div class="meta-item"><strong>Medications:</strong> <span class="muted">{{ implode(', ', $patient->medications ?? []) ?: 'None listed' }}</span></div>
                <div class="meta-item"><strong>Conditions:</strong> <span class="muted">{{ implode(', ', $patient->conditions ?? []) ?: 'None listed' }}</span></div>
            </div>
        </div>
    </div>

    <div class="grid-2" style="margin-top:22px;">
        <div class="table-card">
            <h2 style="margin-top:0;">Uploaded Reports</h2>
            <table>
                <thead><tr><th>Type</th><th>File</th><th>AI Extraction</th></tr></thead>
                <tbody>
                @forelse($patient->documents as $document)
                    <tr>
                        <td>{{ $document->document_type }}</td>
                        <td>{{ $document->original_name }}</td>
                        <td>{{ $document->reportExtractions->count() ? 'Available' : 'Pending' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">No reports uploaded.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-card">
            <h2 style="margin-top:0;">AI Outputs</h2>
            <table>
                <thead><tr><th>Agent</th><th>Confidence</th><th>Review</th></tr></thead>
                <tbody>
                @forelse($outputs as $output)
                    <tr>
                        <td>{{ ucwords(str_replace('_', ' ', $output->agent_type)) }}</td>
                        <td>{{ $output->confidence_score }}</td>
                        <td>{{ $output->doctor_review_status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">No AI outputs available.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid-2" style="margin-top:22px;">
        <div class="card">
            <h2>Suggested Questions</h2>
            <div class="meta-list" style="margin-top:14px;">
                <div class="meta-item">Ask about symptom duration and progression.</div>
                <div class="meta-item">Ask about medication adherence and recent changes.</div>
                <div class="meta-item">Ask about recent labs, family history, and red flag symptoms.</div>
            </div>
        </div>
        <div class="card">
            <h2>Latest SOAP Draft</h2>
            @if($latestSoap)
                <div class="meta-list" style="margin-top:14px;">
                    <div class="meta-item"><strong>Subjective:</strong><div class="muted" style="margin-top:8px;">{{ $latestSoap->subjective }}</div></div>
                    <div class="meta-item"><strong>Objective:</strong><div class="muted" style="margin-top:8px;">{{ $latestSoap->objective }}</div></div>
                    <div class="meta-item"><strong>Assessment:</strong><div class="muted" style="margin-top:8px;">{{ $latestSoap->assessment }}</div></div>
                    <div class="meta-item"><strong>Plan:</strong><div class="muted" style="margin-top:8px;">{{ $latestSoap->plan }}</div></div>
                </div>
            @else
                <div class="meta-item" style="margin-top:14px;">No SOAP draft generated yet.</div>
            @endif
        </div>
    </div>
@endsection
