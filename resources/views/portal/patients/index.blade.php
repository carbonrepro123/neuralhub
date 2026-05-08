@extends('layouts.portal', ['title' => 'Patients'])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Patient Management</h1>
            <div class="page-subtitle">Search records, review documents, check appointments, and inspect AI summaries.</div>
        </div>
        <a href="{{ route('portal.patients.create') }}" class="button">Add Patient</a>
    </div>

    <div class="card">
        <form class="toolbar" method="GET">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by patient name, phone, or email">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="table-card" style="margin-top:18px;">
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Primary Doctor</th>
                <th>Documents</th>
                <th>Appointments</th>
                <th>Consent</th>
            </tr>
            </thead>
            <tbody>
            @forelse($patients as $patient)
                <tr>
                    <td><a href="{{ route('portal.patients.show', $patient) }}"><strong>{{ $patient->name }}</strong></a><div class="muted">{{ $patient->email }}</div></td>
                    <td>{{ $patient->doctor?->user?->name ?: 'Unassigned' }}</td>
                    <td>{{ $patient->documents->count() }}</td>
                    <td>{{ $patient->appointments->count() }}</td>
                    <td><span class="pill">{{ $patient->consent_status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="5">No patients found.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:18px;">{{ $patients->links() }}</div>
    </div>
@endsection
