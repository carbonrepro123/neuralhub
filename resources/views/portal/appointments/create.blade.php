@extends('layouts.portal', ['title' => 'Create Appointment'])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Create Appointment</h1>
            <div class="page-subtitle">Schedule a video or in-person visit and connect it to the correct doctor and patient.</div>
        </div>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('portal.appointments.store') }}">
            @csrf
            <div class="form-grid">
                <div class="field"><label>Clinic ID</label><input type="number" name="clinic_id" value="{{ old('clinic_id', 1) }}" required></div>
                <div class="field"><label>Doctor</label><select name="doctor_id" required>@foreach($doctors as $doctor)<option value="{{ $doctor->id }}">{{ $doctor->user?->name }} ({{ $doctor->specialty }})</option>@endforeach</select></div>
                <div class="field"><label>Patient</label><select name="patient_id" required>@foreach($patients as $patient)<option value="{{ $patient->id }}">{{ $patient->name }}</option>@endforeach</select></div>
                <div class="field"><label>Appointment Type</label><select name="appointment_type"><option value="video">Video</option><option value="in_person">In Person</option></select></div>
                <div class="field"><label>Appointment Date</label><input type="date" name="appointment_date" value="{{ old('appointment_date', now()->format('Y-m-d')) }}" required></div>
                <div class="field"><label>Start Time</label><input type="time" name="start_time" value="{{ old('start_time', '14:00') }}" required></div>
                <div class="field"><label>End Time</label><input type="time" name="end_time" value="{{ old('end_time', '14:30') }}"></div>
                <div class="field full"><label>Reason for Visit</label><textarea name="reason_for_visit">{{ old('reason_for_visit') }}</textarea></div>
                <div class="field full"><label>Internal Notes</label><textarea name="notes">{{ old('notes') }}</textarea></div>
            </div>
            <div class="toolbar" style="margin-top:18px;">
                <button type="submit">Save Appointment</button>
                <a href="{{ route('portal.appointments.index') }}" class="button secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
