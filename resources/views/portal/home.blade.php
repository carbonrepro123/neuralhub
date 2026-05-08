<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ClinixAI Portal</title>
    <link rel="stylesheet" href="{{ asset('clinixai.css') }}">
</head>
<body>
<div class="auth-shell">
    <div class="auth-card" style="width:min(980px,100%);">
        <div class="pill">ClinixAI Laravel Portal</div>
        <h1 class="page-title" style="margin-top:16px;">AI-powered clinic operations and secure care workflows</h1>
        <p class="page-subtitle">
            Manage patients, launch video consultations, review AI summaries, and track doctor compliance renewals from one secure portal.
        </p>
        <div class="grid-3" style="margin-top:24px;">
            <div class="meta-item"><strong>Live Video Consults</strong><div class="muted" style="margin-top:8px;">Daily.co room generation and consultation side panel.</div></div>
            <div class="meta-item"><strong>Compliance Bot</strong><div class="muted" style="margin-top:8px;">Expiry tracking, reminders, and AI renewal checklist support.</div></div>
            <div class="meta-item"><strong>Patient Management</strong><div class="muted" style="margin-top:8px;">Profiles, documents, appointments, and AI review history.</div></div>
        </div>
        <div class="toolbar" style="margin-top:26px;">
            <a class="button" href="{{ route('login') }}">Doctor / Admin Login</a>
            <a class="button secondary" href="{{ route('register') }}">Register User</a>
        </div>
        <div class="meta-item" style="margin-top:24px;">
            <strong>Seeded Admin Access</strong>
            <div class="muted" style="margin-top:8px;">Email: admin@clinixai.com</div>
            <div class="muted">Password: password123</div>
        </div>
    </div>
</div>
</body>
</html>
