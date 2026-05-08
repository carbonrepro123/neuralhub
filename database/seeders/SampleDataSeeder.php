<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ComplianceItem;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $clinic = Clinic::firstOrCreate(
            ['slug' => 'sample-clinic'],
            ['name' => 'Sample Clinic', 'email' => 'ops@sampleclinic.com', 'status' => 'active']
        );

        $adminRole = Role::where('slug', 'super_admin')->first();
        $doctorRole = Role::where('slug', 'doctor')->first();
        $patientRole = Role::where('slug', 'patient')->first();

        User::firstOrCreate(
            ['email' => 'admin@clinixai.com'],
            [
                'name' => 'ClinixAI Admin',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'role_id' => optional($adminRole)->id,
                'status' => 'active',
            ]
        );

        $doctorUser = User::firstOrCreate(
            ['email' => 'doctor@clinixai.test'],
            [
                'name' => 'Dr. Jane Smith',
                'password' => Hash::make('Password123!'),
                'role' => 'doctor',
                'role_id' => optional($doctorRole)->id,
                'status' => 'active',
            ]
        );

        $doctor = Doctor::firstOrCreate(
            ['user_id' => $doctorUser->id],
            [
                'clinic_id' => $clinic->id,
                'specialty' => 'Internal Medicine',
                'license_number' => 'IM-' . Str::upper(Str::random(6)),
                'state' => 'Texas',
                'status' => 'active',
            ]
        );

        $patientUser = User::firstOrCreate(
            ['email' => 'patient@clinixai.test'],
            [
                'name' => 'John Carter',
                'password' => Hash::make('Password123!'),
                'role' => 'patient',
                'role_id' => optional($patientRole)->id,
                'status' => 'active',
            ]
        );

        $patient = Patient::firstOrCreate(
            ['email' => 'patient@clinixai.test'],
            [
                'clinic_id' => $clinic->id,
                'user_id' => $patientUser->id,
                'primary_doctor_id' => $doctor->id,
                'name' => 'John Carter',
                'phone' => '555-0100',
                'gender' => 'male',
                'consent_status' => 'granted',
                'allergies' => ['Penicillin'],
                'medications' => ['Metformin'],
                'conditions' => ['Type 2 Diabetes'],
            ]
        );

        Appointment::firstOrCreate(
            ['clinic_id' => $clinic->id, 'doctor_id' => $doctor->id, 'patient_id' => $patient->id, 'appointment_date' => now()->toDateString()],
            [
                'appointment_type' => 'video',
                'start_time' => '14:00:00',
                'end_time' => '14:30:00',
                'reason_for_visit' => 'Lab review',
                'status' => 'scheduled',
            ]
        );

        ComplianceItem::firstOrCreate(
            ['doctor_id' => $doctor->id, 'certification_type' => 'DEA License'],
            [
                'issuing_authority' => 'DEA',
                'license_number' => 'DEA-99881',
                'state' => 'Texas',
                'issue_date' => now()->subYears(2)->toDateString(),
                'expiry_date' => now()->addDays(60)->toDateString(),
                'renewal_frequency' => '3 years',
                'status' => 'expiring_soon',
                'created_by' => $doctorUser->id,
                'updated_by' => $doctorUser->id,
            ]
        );

        $clinic->users()->syncWithoutDetaching([
            $doctorUser->id => ['role' => 'doctor', 'status' => 'active'],
            $patientUser->id => ['role' => 'patient', 'status' => 'active'],
        ]);
    }
}
