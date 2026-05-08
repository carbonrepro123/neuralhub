@extends('layouts.portal', ['title' => 'Consultation'])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Live Consultation Assistant</h1>
            <div class="page-subtitle">Neural Hub meeting console. AI assistance only. Doctor review required.</div>
        </div>
        <div class="toolbar">
            <form method="POST" action="{{ route('portal.appointments.complete', $appointment) }}">
                @csrf
                <button class="button secondary" type="submit">End Meeting</button>
            </form>
        </div>
    </div>

    <div class="card">
        <h2>Video Consultation</h2>
        @include('portal.appointments._jitsi_embed', [
            'appointment' => $appointment,
            'participantName' => $appointment->doctor?->user?->name ?: 'Doctor',
            'meetingContainerId' => 'doctor-jitsi-container',
            'participantListId' => 'doctor-jitsi-participants',
            'meetingHeight' => 860,
        ])
        <div class="meta-item" style="margin-top:14px;">
            <strong>Important note</strong>
            <div class="muted" style="margin-top:8px;">Public Jitsi is being used for demo mode. True doctor-as-moderator control requires self-hosted Jitsi or a production provider like Daily.</div>
        </div>
    </div>

    <div class="grid-2" style="margin-top:22px;">
        <div class="card">
            <h2>Patient Overview</h2>
            <div class="meta-list" style="margin-top:14px;">
                <div class="meta-item"><strong>Name:</strong> <span class="muted">{{ $patient->name }}</span></div>
                <div class="meta-item"><strong>Age / DOB:</strong> <span class="muted">{{ $patient->date_of_birth ? $patient->date_of_birth->age . ' years · ' . $patient->date_of_birth->format('M d, Y') : 'Not recorded' }}</span></div>
                <div class="meta-item"><strong>Allergies:</strong> <span class="muted">{{ implode(', ', $patient->allergies ?? []) ?: 'None listed' }}</span></div>
                <div class="meta-item"><strong>Medications:</strong> <span class="muted">{{ implode(', ', $patient->medications ?? []) ?: 'None listed' }}</span></div>
                <div class="meta-item"><strong>Conditions:</strong> <span class="muted">{{ implode(', ', $patient->conditions ?? []) ?: 'None listed' }}</span></div>
                <div class="meta-item"><strong>Insurance:</strong> <span class="muted">{{ $patient->insurance_provider ?: 'Not recorded' }}</span></div>
            </div>

            <h2 style="margin-top:22px;">Assigned AI Employees</h2>
            <div class="meta-list" style="margin-top:14px;">
                @forelse($assignedAgents as $agent)
                    <div class="meta-item">
                        <strong>{{ $agent->name }}</strong>
                        <div class="muted" style="margin-top:6px;">{{ ucfirst(str_replace('_', ' ', $agent->agent_type)) }} is enabled to assist this doctor during the visit.</div>
                    </div>
                @empty
                    <div class="meta-item">No AI employees assigned yet.</div>
                @endforelse
            </div>

            <h2 style="margin-top:22px;">Live Participants</h2>
            <div id="doctor-jitsi-participants" class="meta-list" style="margin-top:14px;"></div>
        </div>
        <div class="card">
            <h2>Consultation Notes</h2>
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
            <h2>Report Reader Brief</h2>
            <div class="meta-list" style="margin-top:14px;">
                @forelse($reportInsights as $insight)
                    <div class="meta-item">
                        <strong>{{ $insight->document?->original_name ?: 'Uploaded report' }}</strong>
                        <div class="muted" style="margin-top:8px;">{{ $insight->summary ?: 'No AI summary stored yet.' }}</div>
                        @if(!empty($insight->vitals_json))
                            <div class="muted" style="margin-top:8px;">Vitals: {{ collect($insight->vitals_json)->map(fn ($row) => ($row['name'] ?? 'Value') . ': ' . ($row['value'] ?? ''))->implode(' · ') }}</div>
                        @endif
                        @if(!empty($insight->abnormal_findings_json))
                            <div class="muted" style="margin-top:8px;">Abnormal: {{ collect($insight->abnormal_findings_json)->implode(', ') }}</div>
                        @endif
                    </div>
                @empty
                    <div class="meta-item">No report summaries available yet. Upload reports before the visit or during intake.</div>
                @endforelse
            </div>
        </div>

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
