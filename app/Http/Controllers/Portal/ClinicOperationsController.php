<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AIAgent;
use App\Models\Clinic;
use App\Models\ClinicFeatureFlag;
use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicOperationsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load(['clinics', 'doctor']);
        $clinicIds = $user->role === 'super_admin'
            ? Clinic::pluck('id')
            : $user->clinics->pluck('id');

        $clinics = Clinic::with(['featureFlags', 'doctors.user'])
            ->when($clinicIds->isNotEmpty(), fn ($query) => $query->whereIn('id', $clinicIds))
            ->orderBy('name')
            ->get();

        $doctors = Doctor::with(['user', 'clinic', 'aiAgents'])
            ->when($clinicIds->isNotEmpty(), fn ($query) => $query->whereIn('clinic_id', $clinicIds))
            ->orderBy('id')
            ->get();

        return view('portal.operations.index', [
            'clinics' => $clinics,
            'doctors' => $doctors,
            'agents' => AIAgent::orderBy('name')->get(),
            'featureCatalog' => [
                'video_consultations' => 'Live Video Consultation',
                'ai_assistant' => 'AI Assistant During Call',
                'compliance_bot' => 'Compliance Bot',
                'report_reader' => 'AI Report Reader',
                'clinic_ai_employees' => 'Clinic AI Employees',
            ],
        ]);
    }

    public function assignAgent(Request $request, Doctor $doctor): RedirectResponse
    {
        $data = $request->validate([
            'ai_agent_id' => ['required', 'integer', 'exists:ai_agents,id'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $doctor->aiAgents()->syncWithoutDetaching([
            $data['ai_agent_id'] => [
                'assigned_by' => $request->user()->id,
                'status' => $data['status'] ?? 'active',
                'configuration' => json_encode([
                    'acts_as_staff' => true,
                    'doctor_review_required' => true,
                ]),
            ],
        ]);

        return back()->with('status', 'AI employee assigned to doctor.');
    }

    public function toggleFeature(Request $request, Clinic $clinic): RedirectResponse
    {
        $data = $request->validate([
            'feature_key' => ['required', 'string', 'max:255'],
            'feature_name' => ['required', 'string', 'max:255'],
            'enabled' => ['required', 'boolean'],
        ]);

        ClinicFeatureFlag::updateOrCreate(
            ['clinic_id' => $clinic->id, 'feature_key' => $data['feature_key']],
            [
                'feature_name' => $data['feature_name'],
                'enabled' => (bool) $data['enabled'],
            ]
        );

        return back()->with('status', 'Clinic feature updated.');
    }
}
