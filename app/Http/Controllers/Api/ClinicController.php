<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RecordsAuditLogs;
use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClinicController extends Controller
{
    use RecordsAuditLogs;

    public function index(Request $request): JsonResponse
    {
        $this->recordAudit($request, 'viewed_clinics', 'clinics');

        return response()->json(Clinic::withCount(['doctors', 'patients', 'appointments'])->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
        ]);

        $clinic = Clinic::create([
            ...$data,
            'slug' => Str::slug($data['name']) . '-' . Str::lower(Str::random(4)),
        ]);

        $this->recordAudit($request, 'created_clinic', 'clinics', $clinic->id);

        return response()->json($clinic, 201);
    }

    public function show(Request $request, Clinic $clinic): JsonResponse
    {
        $this->recordAudit($request, 'viewed_clinic', 'clinics', $clinic->id);

        return response()->json($clinic->load(['doctors.user', 'patients']));
    }
}
