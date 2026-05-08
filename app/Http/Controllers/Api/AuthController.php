<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RecordsAuditLogs;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use RecordsAuditLogs;

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
            'role' => ['required', 'in:doctor,patient,clinic_admin'],
            'clinic_name' => ['nullable', 'string'],
            'specialty' => ['nullable', 'string'],
        ]);

        $role = Role::where('slug', $data['role'])->first();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => optional($role)->id,
            'role' => $data['role'],
            'status' => 'active',
        ]);

        $clinic = null;

        if ($data['role'] === 'clinic_admin' && ! empty($data['clinic_name'])) {
            $clinic = Clinic::create([
                'name' => $data['clinic_name'],
                'slug' => Str::slug($data['clinic_name']) . '-' . Str::lower(Str::random(4)),
                'status' => 'active',
            ]);

            $clinic->users()->attach($user->id, ['role' => 'clinic_admin', 'status' => 'active']);
        }

        if ($data['role'] === 'doctor' && $clinic) {
            Doctor::create([
                'user_id' => $user->id,
                'clinic_id' => $clinic->id,
                'specialty' => $data['specialty'] ?? 'Primary Care',
                'status' => 'active',
            ]);
        }

        if ($data['role'] === 'patient' && $clinic) {
            Patient::create([
                'user_id' => $user->id,
                'clinic_id' => $clinic->id,
                'name' => $user->name,
                'email' => $user->email,
                'consent_status' => 'pending',
            ]);
        }

        $token = $user->createToken('portal-token')->plainTextToken;

        $this->recordAudit($request, 'user_registered', 'users', $user->id, ['role' => $user->role]);

        return response()->json([
            'user' => $user,
            'token' => $token,
            'doctor_review_required' => false,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 422);
        }

        $token = $user->createToken('portal-token')->plainTextToken;

        $this->recordAudit($request, 'user_logged_in', 'users', $user->id);

        return response()->json([
            'user' => $user,
            'token' => $token,
            'redirect_role' => $user->role,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        $this->recordAudit($request, 'user_logged_out', 'users', optional($request->user())->id);

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()?->load('clinics', 'doctor', 'roleRecord'));
    }
}
