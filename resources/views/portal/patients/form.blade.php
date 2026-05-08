@php
    $editing = isset($patient);
    $patient = $patient ?? null;
@endphp

<div class="form-grid">
    <div class="field">
        <label>Clinic ID</label>
        <input type="number" name="clinic_id" value="{{ old('clinic_id', $patient->clinic_id ?? 1) }}" required>
    </div>
    <div class="field">
        <label>Primary Doctor</label>
        <select name="primary_doctor_id">
            <option value="">Select doctor</option>
            @foreach($doctors as $doctor)
                <option value="{{ $doctor->id }}" @selected(old('primary_doctor_id', $patient->primary_doctor_id ?? null) == $doctor->id)>{{ $doctor->user?->name }} ({{ $doctor->specialty }})</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label>Name</label>
        <input type="text" name="name" value="{{ old('name', $patient->name ?? '') }}" required>
    </div>
    <div class="field">
        <label>Date of Birth</label>
        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($patient?->date_of_birth)->format('Y-m-d')) }}">
    </div>
    <div class="field">
        <label>Gender</label>
        <input type="text" name="gender" value="{{ old('gender', $patient->gender ?? '') }}">
    </div>
    <div class="field">
        <label>Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $patient->phone ?? '') }}">
    </div>
    <div class="field">
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email', $patient->email ?? '') }}">
    </div>
    <div class="field">
        <label>Insurance Provider</label>
        <input type="text" name="insurance_provider" value="{{ old('insurance_provider', $patient->insurance_provider ?? '') }}">
    </div>
    <div class="field">
        <label>Insurance Member ID</label>
        <input type="text" name="insurance_member_id" value="{{ old('insurance_member_id', $patient->insurance_member_id ?? '') }}">
    </div>
    <div class="field">
        <label>Consent Status</label>
        <select name="consent_status">
            @foreach(['pending', 'granted', 'revoked'] as $status)
                <option value="{{ $status }}" @selected(old('consent_status', $patient->consent_status ?? 'pending') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>
    <div class="field full">
        <label>Address</label>
        <textarea name="address">{{ old('address', $patient->address ?? '') }}</textarea>
    </div>
    <div class="field full">
        <label>Medical History</label>
        <textarea name="medical_history">{{ old('medical_history', isset($patient) ? implode("\n", $patient->medical_history ?? []) : '') }}</textarea>
    </div>
    <div class="field full">
        <label>Allergies</label>
        <textarea name="allergies">{{ old('allergies', isset($patient) ? implode("\n", $patient->allergies ?? []) : '') }}</textarea>
    </div>
    <div class="field full">
        <label>Medications</label>
        <textarea name="medications">{{ old('medications', isset($patient) ? implode("\n", $patient->medications ?? []) : '') }}</textarea>
    </div>
    <div class="field full">
        <label>Conditions</label>
        <textarea name="conditions">{{ old('conditions', isset($patient) ? implode("\n", $patient->conditions ?? []) : '') }}</textarea>
    </div>
    <div class="field full">
        <label>Notes</label>
        <textarea name="notes">{{ old('notes', $patient->notes ?? '') }}</textarea>
    </div>
</div>

<div class="toolbar" style="margin-top:18px;">
    <button type="submit">{{ $editing ? 'Update Patient' : 'Create Patient' }}</button>
    <a href="{{ route('portal.patients.index') }}" class="button secondary">Cancel</a>
</div>
