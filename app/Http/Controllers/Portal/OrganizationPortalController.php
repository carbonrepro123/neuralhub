<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrganizationPortalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('clinics');
        $clinicIds = $user->role === 'super_admin'
            ? Clinic::pluck('id')
            : $user->clinics->pluck('id');

        return view('portal.organizations.index', [
            'clinics' => Clinic::with(['users', 'doctors.user', 'patients'])
                ->when($clinicIds->isNotEmpty(), fn ($query) => $query->whereIn('id', $clinicIds))
                ->orderBy('name')
                ->get(),
            'roles' => Role::whereIn('slug', ['clinic_admin', 'doctor', 'staff', 'patient'])->orderBy('slug')->get(),
        ]);
    }

    public function storeClinic(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
        ]);

        Clinic::create([
            ...$data,
            'slug' => Str::slug($data['name']) . '-' . Str::lower(Str::random(4)),
            'status' => 'active',
        ]);

        return back()->with('status', 'Clinic created successfully.');
    }

    public function storeMember(Request $request, Clinic $clinic): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'in:clinic_admin,doctor,staff,patient'],
            'specialty' => ['nullable', 'string', 'max:255'],
        ]);

        $role = Role::where('slug', $data['role'])->first();
        $password = 'Password123!';

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'role_id' => optional($role)->id,
            'status' => 'active',
            'password' => Hash::make($password),
        ]);

        $clinic->users()->syncWithoutDetaching([
            $user->id => ['role' => $data['role'], 'status' => 'active'],
        ]);

        if ($data['role'] === 'doctor') {
            Doctor::create([
                'user_id' => $user->id,
                'clinic_id' => $clinic->id,
                'specialty' => $data['specialty'] ?: 'Primary Care',
                'status' => 'active',
            ]);
        }

        if ($data['role'] === 'patient') {
            $clinic->patients()->create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'consent_status' => 'pending',
            ]);
        }

        return back()->with('status', "Team member added. Temporary password: {$password}");
    }
}
