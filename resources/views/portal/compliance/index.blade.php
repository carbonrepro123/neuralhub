@extends('layouts.portal', ['title' => 'Compliance Bot'])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Compliance Bot</h1>
            <div class="page-subtitle">Track doctor licenses, certifications, reminder schedules, and AI renewal checklists.</div>
        </div>
        <a href="{{ route('portal.compliance.create') }}" class="button">Add Compliance Item</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><div class="stat-label">Active Certifications</div><div class="stat-value">{{ $stats['active'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Expiring in 90 Days</div><div class="stat-value">{{ $stats['expiring_90'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Expired</div><div class="stat-value">{{ $stats['expired'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Missing Documents</div><div class="stat-value">{{ $stats['missing_documents'] }}</div></div>
    </div>

    <div class="table-card" style="margin-top:22px;">
        <table>
            <thead>
            <tr>
                <th>Certification</th>
                <th>Doctor</th>
                <th>Expiry</th>
                <th>Status</th>
                <th>Open</th>
            </tr>
            </thead>
            <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $item->certification_type }}</td>
                    <td>{{ $item->doctor?->user?->name }}</td>
                    <td>{{ optional($item->expiry_date)->format('M d, Y') ?: 'N/A' }}</td>
                    <td><span class="pill {{ $item->status === 'expired' ? 'danger' : 'warn' }}">{{ $item->status }}</span></td>
                    <td><a href="{{ route('portal.compliance.show', $item) }}">View</a></td>
                </tr>
            @empty
                <tr><td colspan="5">No compliance items available.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:18px;">{{ $items->links() }}</div>
    </div>
@endsection
