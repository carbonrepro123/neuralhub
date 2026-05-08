<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Neural Hub') }}</title>
    <link rel="stylesheet" href="{{ asset('clinixai.css') }}">
</head>
<body>
<div class="shell">
    <div class="topbar">
        <div>
            <div class="brand-title">Neural Hub</div>
            <div class="brand-subtitle">Secure clinical operations and doctor workflow support</div>
        </div>
        <div class="toolbar">
            <div class="pill">{{ ucfirst(str_replace('_', ' ', auth()->user()->role ?? 'user')) }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="button secondary" type="submit">Logout</button>
            </form>
        </div>
    </div>
    <div class="layout">
        <aside class="sidebar">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('portal.patients.index') }}" class="nav-link {{ request()->routeIs('portal.patients.*') ? 'active' : '' }}">Patients</a>
            <a href="{{ route('portal.appointments.index') }}" class="nav-link {{ request()->routeIs('portal.appointments.*') ? 'active' : '' }}">Appointments</a>
            <a href="{{ route('portal.compliance.index') }}" class="nav-link {{ request()->routeIs('portal.compliance.*') ? 'active' : '' }}">Compliance</a>
            <a href="{{ route('portal.ai.index') }}" class="nav-link {{ request()->routeIs('portal.ai.*') ? 'active' : '' }}">AI Activity</a>
            <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">Profile</a>
        </aside>
        <main class="content">
            @isset($header)
                <div class="page-header">
                    <div>{{ $header }}</div>
                </div>
            @endisset
            @if (session('status'))
                <div class="notice">{{ session('status') }}</div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
