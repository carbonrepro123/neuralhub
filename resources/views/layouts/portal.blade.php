<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ClinixAI Portal' }}</title>
    <link rel="stylesheet" href="{{ asset('clinixai.css') }}">
</head>
<body>
<div class="shell">
    <div class="topbar">
        <div>
            <div class="brand-title">ClinixAI</div>
            <div class="brand-subtitle">Secure operations portal for clinics, doctors, staff, and patients</div>
        </div>
        @auth
            <div class="toolbar">
                <div class="pill">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</div>
                <a class="button secondary" href="{{ route('profile.edit') }}">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="button secondary" type="submit">Logout</button>
                </form>
            </div>
        @endauth
    </div>
    <div class="layout">
        <aside class="sidebar">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('portal.patients.index') }}" class="nav-link {{ request()->routeIs('portal.patients.*') ? 'active' : '' }}">Patients</a>
            <a href="{{ route('portal.appointments.index') }}" class="nav-link {{ request()->routeIs('portal.appointments.*') ? 'active' : '' }}">Appointments</a>
            <a href="{{ route('portal.compliance.index') }}" class="nav-link {{ request()->routeIs('portal.compliance.*') ? 'active' : '' }}">Compliance Bot</a>
            <a href="{{ route('portal.ai.index') }}" class="nav-link {{ request()->routeIs('portal.ai.*') ? 'active' : '' }}">AI Agents</a>

            <div class="sidebar-section">Quick Links</div>
            <a href="{{ route('portal.appointments.create') }}" class="nav-link">Create Appointment</a>
            <a href="{{ route('portal.patients.create') }}" class="nav-link">Add Patient</a>
            <a href="{{ route('portal.compliance.create') }}" class="nav-link">Add Compliance Item</a>
        </aside>
        <main class="content">
            @if (session('status'))
                <div class="notice">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="notice error-notice">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="notice error-notice">
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
