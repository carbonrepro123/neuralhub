<x-guest-layout>
    <div class="pill">ClinixAI Login</div>
    <h1 class="page-title" style="font-size: 32px; margin-top: 18px;">Sign in to the secure portal</h1>
    <p class="page-subtitle">Doctor, clinic admin, staff, and patient access starts here.</p>

    @if (session('status'))
        <div class="notice" style="margin-top:16px;">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" style="margin-top: 18px;">
        @csrf
        <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>
        <div class="field" style="margin-top:14px;">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
        </div>
        <div class="toolbar" style="margin-top:18px; justify-content:space-between;">
            <label class="muted"><input type="checkbox" name="remember"> Remember me</label>
            @if (Route::has('password.request'))
                <a class="muted" href="{{ route('password.request') }}">Forgot password?</a>
            @endif
        </div>
        <div class="toolbar" style="margin-top:18px;">
            <button type="submit">Log in</button>
            <a class="button secondary" href="{{ route('register') }}">Register</a>
        </div>
    </form>

    <div class="meta-item" style="margin-top:22px;">
        <strong>Seeded access</strong>
        <div class="muted" style="margin-top:8px;">Admin: admin@clinixai.com / password123</div>
        <div class="muted">Doctor sample: doctor@clinixai.test / Password123!</div>
    </div>
</x-guest-layout>
