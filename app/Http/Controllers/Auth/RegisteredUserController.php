<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use App\Support\Portal;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', [
            'roles' => Role::whereIn('slug', ['patient', 'doctor', 'clinic_admin'])->get(),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role' => ['required', 'in:patient,doctor,clinic_admin'],
            'clinic_name' => ['nullable', 'string', 'max:255'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $role = Role::where('slug', $request->string('role')->toString())->first();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->string('role')->toString(),
            'role_id' => optional($role)->id,
            'status' => 'active',
            'password' => Hash::make($request->password),
        ]);

        if ($user->role === 'patient') {
            Patient::create([
                'user_id' => $user->id,
                'clinic_id' => 1,
                'name' => $user->name,
                'email' => $user->email,
                'consent_status' => 'pending',
            ]);
        }

        if (in_array($user->role, ['doctor', 'clinic_admin'], true)) {
            $clinic = $this->resolveClinic($request);

            $clinic->users()->syncWithoutDetaching([
                $user->id => ['role' => $user->role, 'status' => 'active'],
            ]);

            if ($user->role === 'doctor') {
                Doctor::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'clinic_id' => $clinic->id,
                        'specialty' => $request->string('specialty')->toString() ?: 'Primary Care',
                        'status' => 'active',
                    ]
                );
            }
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route(Portal::roleDashboardRoute($user), absolute: false));
    }

    private function resolveClinic(Request $request): Clinic
    {
        $clinicName = $request->string('clinic_name')->toString();

        if ($clinicName !== '') {
            return Clinic::firstOrCreate(
                ['slug' => Str::slug($clinicName)],
                ['name' => $clinicName, 'status' => 'active']
            );
        }

        return Clinic::firstOrCreate(
            ['slug' => 'sample-clinic'],
            ['name' => 'Sample Clinic', 'status' => 'active']
        );
    }
}
