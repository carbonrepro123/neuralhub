@extends('layouts.portal', ['title' => 'Appointments'])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Appointments</h1>
            <div class="page-subtitle">Manage scheduled visits, video consultations, status changes, and Daily.co room readiness.</div>
        </div>
        <a href="{{ route('portal.appointments.create') }}" class="button">Create Appointment</a>
    </div>

    <div class="table-card">
        <table>
            <thead>
            <tr>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Date</th>
                <th>Type</th>
                <th>Status</th>
                <th>Join</th>
                <th>Open</th>
            </tr>
            </thead>
            <tbody>
            @forelse($appointments as $appointment)
                <tr>
                    <td>{{ $userRole === 'patient' ? ($appointment->doctor?->user?->name ?: 'Assigned Doctor') : $appointment->patient?->name }}</td>
                    <td>{{ $appointment->doctor?->user?->name }}</td>
                    <td>{{ optional($appointment->appointment_date)->format('M d, Y') }} {{ $appointment->start_time }}</td>
                    <td>{{ ucfirst($appointment->appointment_type) }}</td>
                    <td><span class="pill {{ in_array($appointment->status, ['waiting','scheduled']) ? 'warn' : 'success' }}">{{ $appointment->status }}</span></td>
                    <td>
                        @if($appointment->appointment_type === 'video' && $appointment->videoRoom?->room_url)
                            @if($userRole === 'patient')
                                <a class="button secondary" href="{{ $appointment->videoRoom->patient_join_url }}" target="_blank">Join</a>
                            @else
                                <a class="button secondary" href="{{ route('portal.appointments.consult', $appointment) }}">Join</a>
                            @endif
                        @else
                            <span class="muted">Not ready</span>
                        @endif
                    </td>
                    <td><a href="{{ route('portal.appointments.show', $appointment) }}">View</a></td>
                </tr>
            @empty
                <tr><td colspan="7">No appointments available.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:18px;">{{ $appointments->links() }}</div>
    </div>
@endsection
