@extends('layouts.portal', ['title' => 'Organizations'])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">ClinixAI Organizations</h1>
            <div class="page-subtitle">Super admin creates clinics, then each clinic manages doctors, staff, and patients inside its own workspace.</div>
        </div>
    </div>

    <div class="grid-2">
        <div class="card">
            <h2>Create Clinic</h2>
            <form method="POST" action="{{ route('portal.organizations.clinics.store') }}" style="margin-top:16px;">
                @csrf
                <div class="form-grid">
                    <div class="field"><label>Clinic Name</label><input name="name" required></div>
                    <div class="field"><label>Email</label><input name="email" type="email"></div>
                    <div class="field"><label>Phone</label><input name="phone"></div>
                    <div class="field"><label>City</label><input name="city"></div>
                    <div class="field"><label>State</label><input name="state"></div>
                </div>
                <div class="toolbar" style="margin-top:18px;">
                    <button type="submit">Create Clinic</button>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>Clinic SaaS Model</h2>
            <div class="meta-list" style="margin-top:16px;">
                <div class="meta-item"><strong>Super Admin</strong><div class="muted" style="margin-top:6px;">Creates clinics, enables features, and oversees all subscriptions.</div></div>
                <div class="meta-item"><strong>Clinic Admin</strong><div class="muted" style="margin-top:6px;">Adds doctors, nurses, assistants, and support staff into the clinic portal.</div></div>
                <div class="meta-item"><strong>Doctor</strong><div class="muted" style="margin-top:6px;">Manages own patients, consults, compliance, and AI review queue.</div></div>
                <div class="meta-item"><strong>AI Employees</strong><div class="muted" style="margin-top:6px;">Receptionist, intake, report reader, compliance bot, and scribe can work on behalf of the clinic team.</div></div>
            </div>
        </div>
    </div>

    <div class="table-card" style="margin-top:22px;">
        <h2 style="margin-top:0;">Clinic Directory</h2>
        @foreach($clinics as $clinic)
            <div class="card" style="margin-top:18px;">
                <div class="page-header" style="margin-bottom:14px;">
                    <div>
                        <h3 style="margin:0;">{{ $clinic->name }}</h3>
                        <div class="page-subtitle">{{ $clinic->city ?: 'City not set' }}{{ $clinic->state ? ', ' . $clinic->state : '' }} · {{ $clinic->email ?: 'No email set' }}</div>
                    </div>
                    <div class="pill success">{{ $clinic->status }}</div>
                </div>

                <div class="stats-grid" style="grid-template-columns:repeat(3,minmax(0,1fr)); margin-bottom:18px;">
                    <div class="stat-card"><div class="stat-label">Doctors</div><div class="stat-value">{{ $clinic->doctors->count() }}</div></div>
                    <div class="stat-card"><div class="stat-label">Team Members</div><div class="stat-value">{{ $clinic->users->count() }}</div></div>
                    <div class="stat-card"><div class="stat-label">Patients</div><div class="stat-value">{{ $clinic->patients->count() }}</div></div>
                </div>

                <div class="grid-2">
                    <div>
                        <h4 style="margin:0 0 10px;">Team</h4>
                        <div class="meta-list">
                            @forelse($clinic->users as $member)
                                <div class="meta-item">
                                    <strong>{{ $member->name }}</strong>
                                    <div class="muted" style="margin-top:6px;">{{ ucfirst(str_replace('_', ' ', $member->pivot->role)) }} · {{ $member->email }}</div>
                                </div>
                            @empty
                                <div class="meta-item">No team members yet.</div>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h4 style="margin:0 0 10px;">Add Team Member</h4>
                        <form method="POST" action="{{ route('portal.organizations.members.store', $clinic) }}">
                            @csrf
                            <div class="form-grid">
                                <div class="field"><label>Name</label><input name="name" required></div>
                                <div class="field"><label>Email</label><input name="email" type="email" required></div>
                                <div class="field"><label>Phone</label><input name="phone"></div>
                                <div class="field">
                                    <label>Role</label>
                                    <select name="role">
                                        @foreach($roles as $role)
                                            <option value="{{ $role->slug }}">{{ ucfirst(str_replace('_', ' ', $role->slug)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field"><label>Specialty (for doctors)</label><input name="specialty"></div>
                            </div>
                            <div class="toolbar" style="margin-top:14px;">
                                <button type="submit">Add to Clinic</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
