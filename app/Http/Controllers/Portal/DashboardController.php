<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AIAgentTask;
use App\Models\AIOutput;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ComplianceItem;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load(['doctor', 'patient', 'clinics']);

        $doctorId = optional($user->doctor)->id;
        $patientId = optional($user->patient)->id;
        $clinicIds = $user->clinics->pluck('id');
        $hasAiOutputsTable = Schema::hasTable('ai_outputs');
        $hasAiAgentTasksTable = Schema::hasTable('ai_agent_tasks');

        $stats = [
            'today_video_calls' => Appointment::query()
                ->when($doctorId, fn ($query) => $query->where('doctor_id', $doctorId))
                ->whereDate('appointment_date', today())
                ->where('appointment_type', 'video')
                ->count(),
            'patients_waiting' => Appointment::query()
                ->when($doctorId, fn ($query) => $query->where('doctor_id', $doctorId))
                ->where('status', 'waiting')
                ->count(),
            'ai_reviews_pending' => $hasAiOutputsTable
                ? AIOutput::query()
                    ->when($doctorId, fn ($query) => $query->where('doctor_id', $doctorId))
                    ->where('doctor_review_status', 'pending')
                    ->count()
                : 0,
            'compliance_expiring' => ComplianceItem::query()
                ->when($doctorId, fn ($query) => $query->where('doctor_id', $doctorId))
                ->whereDate('expiry_date', '<=', now()->addDays(90))
                ->count(),
            'total_patients' => Patient::query()
                ->when($doctorId, fn ($query) => $query->where('primary_doctor_id', $doctorId))
                ->when($clinicIds->isNotEmpty(), fn ($query) => $query->whereIn('clinic_id', $clinicIds))
                ->when($patientId, fn ($query) => $query->where('id', $patientId))
                ->count(),
            'active_clinics' => $user->role === 'super_admin' ? Clinic::count() : $clinicIds->count(),
        ];

        return view('portal.dashboard', [
            'user' => $user,
            'stats' => $stats,
            'recentPatients' => Patient::query()
                ->when($doctorId, fn ($query) => $query->where('primary_doctor_id', $doctorId))
                ->when($clinicIds->isNotEmpty(), fn ($query) => $query->whereIn('clinic_id', $clinicIds))
                ->latest()
                ->take(5)
                ->get(),
            'recentAppointments' => Appointment::with(['patient', 'doctor.user'])
                ->when($doctorId, fn ($query) => $query->where('doctor_id', $doctorId))
                ->latest('appointment_date')
                ->take(5)
                ->get(),
            'recentAiTasks' => $hasAiAgentTasksTable
                ? AIAgentTask::query()
                    ->when($doctorId, fn ($query) => $query->where('doctor_id', $doctorId))
                    ->latest()
                    ->take(6)
                    ->get()
                : collect(),
            'expiringCompliance' => ComplianceItem::query()
                ->when($doctorId, fn ($query) => $query->where('doctor_id', $doctorId))
                ->whereDate('expiry_date', '<=', now()->addDays(90))
                ->orderBy('expiry_date')
                ->take(5)
                ->get(),
        ]);
    }
}
