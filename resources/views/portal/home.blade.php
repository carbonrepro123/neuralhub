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
        <div class="pill">ClinixAI Clinic OS</div>
        <h1 class="page-title" style="margin-top:16px;">AI-powered operations platform for clinics, doctors, and care teams</h1>
        <p class="page-subtitle">
            Super admins onboard clinics. Clinic admins add doctors, nurses, assistants, and patients. AI employees help book appointments, prepare visits, review reports, and support compliance from one secure matte healthcare portal.
        </p>
        <div class="grid-3" style="margin-top:24px;">
            <div class="meta-item"><strong>Clinic SaaS Control</strong><div class="muted" style="margin-top:8px;">Create clinics, add doctors and staff, and turn on the right modules per customer account.</div></div>
            <div class="meta-item"><strong>Video Consult + AI Assistant</strong><div class="muted" style="margin-top:8px;">Launch Jitsi or Daily video calls and show the doctor a live patient brief, reports, and SOAP drafting help.</div></div>
            <div class="meta-item"><strong>Compliance + Patient Ops</strong><div class="muted" style="margin-top:8px;">Track doctor renewals, upload certifications, manage patients, and review AI report analysis.</div></div>
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
