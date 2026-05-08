<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Neural Hub Meeting Join</title>
    <link rel="stylesheet" href="{{ asset('clinixai.css') }}">
</head>
<body>
<div class="auth-shell" style="align-items:flex-start;">
    <div class="auth-card" style="width:min(1200px,100%);margin-top:28px;">
        <div class="pill">Neural Hub</div>
        <h1 class="page-title" style="margin-top:16px;">Patient Join Room</h1>
        <div class="page-subtitle">Join your appointment with {{ $appointment->doctor?->user?->name ?: 'your doctor' }} directly from Neural Hub.</div>

        @if($appointment->status !== 'in_progress')
            <div class="card" style="margin-top:24px;">
                <h2>Doctor has not started the meeting yet</h2>
                <div class="muted" style="margin-top:10px;">Please keep this page open. Once the doctor starts the meeting, refresh and the video room will appear here.</div>
            </div>
        @else
            <div class="split" style="margin-top:24px;">
                <div class="card">
                    <h2>Live Meeting</h2>
                    @include('portal.appointments._jitsi_embed', [
                        'appointment' => $appointment,
                        'participantName' => $appointment->patient?->name ?: 'Patient',
                        'meetingContainerId' => 'public-jitsi-container',
                        'participantListId' => 'public-jitsi-participants',
                    ])
                </div>
                <div class="card">
                    <h2>Visit Information</h2>
                    <div class="meta-list" style="margin-top:14px;">
                        <div class="meta-item"><strong>Doctor</strong><div class="muted" style="margin-top:6px;">{{ $appointment->doctor?->user?->name ?: 'Assigned doctor' }}</div></div>
                        <div class="meta-item"><strong>Date</strong><div class="muted" style="margin-top:6px;">{{ optional($appointment->appointment_date)->format('M d, Y') }} {{ $appointment->start_time }}</div></div>
                        <div class="meta-item"><strong>Status</strong><div class="muted" style="margin-top:6px;">{{ $appointment->status }}</div></div>
                    </div>

                    <h2 style="margin-top:22px;">Live Participants</h2>
                    <div id="public-jitsi-participants" class="meta-list" style="margin-top:14px;"></div>
                </div>
            </div>
        @endif
    </div>
</div>
</body>
</html>
