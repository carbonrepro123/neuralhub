@extends('layouts.portal', ['title' => 'Add Compliance Item'])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Add Compliance Item</h1>
            <div class="page-subtitle">Create a tracked certification, license, or credential renewal item.</div>
        </div>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('portal.compliance.store') }}">
            @csrf
            <div class="form-grid">
                <div class="field"><label>Doctor</label><select name="doctor_id">@foreach($doctors as $doctor)<option value="{{ $doctor->id }}">{{ $doctor->user?->name }} ({{ $doctor->specialty }})</option>@endforeach</select></div>
                <div class="field"><label>Certification Type</label><input type="text" name="certification_type" required></div>
                <div class="field"><label>Issuing Authority</label><input type="text" name="issuing_authority"></div>
                <div class="field"><label>License Number</label><input type="text" name="license_number"></div>
                <div class="field"><label>State</label><input type="text" name="state"></div>
                <div class="field"><label>Renewal Frequency</label><input type="text" name="renewal_frequency"></div>
                <div class="field"><label>Issue Date</label><input type="date" name="issue_date"></div>
                <div class="field"><label>Expiry Date</label><input type="date" name="expiry_date"></div>
                <div class="field full"><label>Notes</label><textarea name="notes"></textarea></div>
            </div>
            <div class="toolbar" style="margin-top:18px;">
                <button type="submit">Save Compliance Item</button>
                <a href="{{ route('portal.compliance.index') }}" class="button secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
