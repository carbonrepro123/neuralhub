@extends('layouts.portal', ['title' => $patient->name])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $patient->name }}</h1>
            <div class="page-subtitle">Patient overview, uploaded reports, AI summaries, appointments, and doctor notes.</div>
        </div>
        <div class="toolbar">
            <a href="{{ route('portal.patients.edit', $patient) }}" class="button secondary">Edit</a>
            <a href="{{ route('portal.appointments.create') }}" class="button">Book Appointment</a>
        </div>
    </div>

    <div class="split">
        <div class="card">
            <h2>Patient Overview</h2>
            <div class="meta-list" style="margin-top:14px;">
                <div class="meta-item"><strong>Date of Birth:</strong> <span class="muted">{{ optional($patient->date_of_birth)->format('M d, Y') ?: 'N/A' }}</span></div>
                <div class="meta-item"><strong>Gender:</strong> <span class="muted">{{ $patient->gender ?: 'N/A' }}</span></div>
                <div class="meta-item"><strong>Primary Doctor:</strong> <span class="muted">{{ $patient->doctor?->user?->name ?: 'Unassigned' }}</span></div>
                <div class="meta-item"><strong>Insurance:</strong> <span class="muted">{{ $patient->insurance_provider ?: 'N/A' }} {{ $patient->insurance_member_id ? '· '.$patient->insurance_member_id : '' }}</span></div>
                <div class="meta-item"><strong>Allergies:</strong> <span class="muted">{{ implode(', ', $patient->allergies ?? []) ?: 'None listed' }}</span></div>
                <div class="meta-item"><strong>Medications:</strong> <span class="muted">{{ implode(', ', $patient->medications ?? []) ?: 'None listed' }}</span></div>
                <div class="meta-item"><strong>Conditions:</strong> <span class="muted">{{ implode(', ', $patient->conditions ?? []) ?: 'None listed' }}</span></div>
            </div>
        </div>

        <div class="card">
            <h2>Upload Report / Document</h2>
            <form method="POST" action="{{ route('portal.patients.documents.store', $patient) }}" enctype="multipart/form-data" style="margin-top:14px;">
                @csrf
                <div class="field">
                    <label>Document Type</label>
                    <select name="document_type">
                        <option value="Lab report PDF">Lab report PDF</option>
                        <option value="Imaging report PDF">Imaging report PDF</option>
                        <option value="Prescription">Prescription</option>
                        <option value="Discharge summary">Discharge summary</option>
                        <option value="Insurance card">Insurance card</option>
                        <option value="ID document">ID document</option>
                        <option value="Referral document">Referral document</option>
                    </select>
                </div>
                <div class="field" style="margin-top:14px;">
                    <label>Choose File</label>
                    <input type="file" name="document" required>
                </div>
                <div class="toolbar" style="margin-top:18px;">
                    <button type="submit">Upload Securely</button>
                </div>
            </form>
        </div>
    </div>

    <div class="grid-2" style="margin-top:22px;">
        <div class="table-card">
            <h2 style="margin-top:0;">Patient Documents</h2>
            <table>
                <thead>
                <tr>
                    <th>Type</th>
                    <th>File</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($patient->documents as $document)
                    <tr>
                        <td>{{ $document->document_type }}</td>
                        <td>{{ $document->original_name }}</td>
                        <td>
                            <div class="toolbar">
                                <a class="button secondary" href="{{ route('portal.patients.documents.download', [$patient, $document]) }}">Download</a>
                                <form method="POST" action="{{ route('portal.patients.documents.analyze', [$patient, $document]) }}">
                                    @csrf
                                    <button type="submit">Analyze with AI</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3">No documents uploaded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-card">
            <h2 style="margin-top:0;">AI Summaries and Notes</h2>
            <table>
                <thead>
                <tr>
                    <th>Agent</th>
                    <th>Status</th>
                    <th>Review</th>
                </tr>
                </thead>
                <tbody>
                @forelse($aiOutputs as $output)
                    <tr>
                        <td>{{ ucwords(str_replace('_', ' ', $output->agent_type)) }}</td>
                        <td>{{ $output->status }}</td>
                        <td><span class="pill warn">{{ $output->doctor_review_status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3">No AI outputs yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="table-card" style="margin-top:22px;">
        <h2 style="margin-top:0;">Appointment History</h2>
        <table>
            <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Status</th>
                <th>Visit</th>
            </tr>
            </thead>
            <tbody>
            @forelse($patient->appointments as $appointment)
                <tr>
                    <td>{{ optional($appointment->appointment_date)->format('M d, Y') }} {{ $appointment->start_time }}</td>
                    <td>{{ ucfirst($appointment->appointment_type) }}</td>
                    <td>{{ $appointment->status }}</td>
                    <td><a href="{{ route('portal.appointments.show', $appointment) }}">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="4">No appointments yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
