<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Neural Hub Login' }}</title>
    <link rel="stylesheet" href="{{ asset('clinixai.css') }}">
</head>
<body>
<div class="auth-shell">
    <div class="auth-card">
        @if ($errors->any())
            <div class="notice error-notice">{{ $errors->first() }}</div>
        @endif
        {{ $slot }}
    </div>
</div>
</body>
</html>
