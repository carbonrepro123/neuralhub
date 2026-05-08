@extends('layouts.portal', ['title' => 'Compliance Item'])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $item->certification_type }}</h1>
            <div class="page-subtitle">{{ $item->doctor?->user?->name }} · {{ $item->status }}</div>
        </div>
        <div class="toolbar">
            <form method="POST" action="{{ route('portal.compliance.analyze', $item) }}">
                @csrf
                <button type="submit">Run Compliance AI</button>
            </form>
        </div>
    </div>

    <div class="split">
        <div class="card">
            <h2>Compliance Details</h2>
            <div class="meta-list" style="margin-top:14px;">
                <div class="meta-item"><strong>Issuing Authority:</strong> <span class="muted">{{ $item->issuing_authority ?: 'N/A' }}</span></div>
                <div class="meta-item"><strong>License Number:</strong> <span class="muted">{{ $item->license_number ?: 'N/A' }}</span></div>
                <div class="meta-item"><strong>Issue Date:</strong> <span class="muted">{{ optional($item->issue_date)->format('M d, Y') ?: 'N/A' }}</span></div>
                <div class="meta-item"><strong>Expiry Date:</strong> <span class="muted">{{ optional($item->expiry_date)->format('M d, Y') ?: 'N/A' }}</span></div>
                <div class="meta-item"><strong>Renewal Frequency:</strong> <span class="muted">{{ $item->renewal_frequency ?: 'N/A' }}</span></div>
                <div class="meta-item"><strong>Notes:</strong> <span class="muted">{{ $item->notes ?: 'N/A' }}</span></div>
            </div>
        </div>
        <div class="card">
            <h2>Upload Compliance Document</h2>
            <form method="POST" action="{{ route('portal.compliance.documents.store', $item) }}" enctype="multipart/form-data" style="margin-top:14px;">
                @csrf
                <div class="field">
                    <label>Choose File</label>
                    <input type="file" name="document" required>
                </div>
                <div class="toolbar" style="margin-top:18px;">
                    <button type="submit">Upload</button>
                </div>
            </form>
        </div>
    </div>

    <div class="grid-2" style="margin-top:22px;">
        <div class="table-card">
            <h2 style="margin-top:0;">Documents</h2>
            <table>
                <thead><tr><th>File</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($documents as $document)
                    <tr>
                        <td>{{ $document->original_name }}</td>
                        <td>{{ $document->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2">No documents uploaded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-card">
            <h2 style="margin-top:0;">Reminder Schedule</h2>
            <table>
                <thead><tr><th>Send At</th><th>Channel</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($item->reminders as $reminder)
                    <tr>
                        <td>{{ optional($reminder->send_at)->format('M d, Y') }}</td>
                        <td>{{ $reminder->channel }}</td>
                        <td>{{ $reminder->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">No reminders yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
