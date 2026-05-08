<x-guest-layout>
    <div class="pill">ClinixAI Registration</div>
    <h1 class="page-title" style="font-size: 32px; margin-top: 18px;">Create portal access</h1>
    <p class="page-subtitle">Use this for patient, doctor, or clinic admin onboarding in the MVP.</p>

    <form method="POST" action="{{ route('register') }}" style="margin-top:18px;">
        @csrf
        <div class="field">
            <label for="name">Full Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
        </div>
        <div class="field" style="margin-top:14px;">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div class="field" style="margin-top:14px;">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                @foreach($roles as $role)
                    <option value="{{ $role->slug }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field" style="margin-top:14px;">
            <label for="clinic_name">Clinic Name</label>
            <input id="clinic_name" type="text" name="clinic_name" value="{{ old('clinic_name') }}" placeholder="Optional for doctor / clinic admin">
        </div>
        <div class="field" style="margin-top:14px;">
            <label for="specialty">Doctor Specialty</label>
            <input id="specialty" type="text" name="specialty" value="{{ old('specialty') }}" placeholder="Used for doctor registration">
        </div>
        <div class="field" style="margin-top:14px;">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>
        </div>
        <div class="field" style="margin-top:14px;">
            <label for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required>
        </div>
        <div class="toolbar" style="margin-top:18px;">
            <button type="submit">Create Account</button>
            <a class="button secondary" href="{{ route('login') }}">Already registered?</a>
        </div>
    </form>
</x-guest-layout>
