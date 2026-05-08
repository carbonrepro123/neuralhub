<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $specialties = [
            'Primary Care',
            'Internal Medicine',
            'Family Medicine',
            'Cardiologist',
            'Neurologist',
            'Orthopedic Surgeon',
            'Oncologist',
            'Pediatrician',
            'Radiologist',
            'Emergency Medicine',
            'Psychiatrist',
            'Dermatologist',
            'Plastic Surgeon',
            'Anesthesiologist',
            'OB/GYN',
            'Endocrinologist',
            'Gastroenterologist',
            'Pulmonologist',
            'Nephrologist',
            'Urologist',
            'Infectious Disease',
            'Hospitalist',
        ];

        foreach ($specialties as $specialty) {
            DB::table('ai_agents')->updateOrInsert(
                ['agent_type' => 'specialty_reference_' . str($specialty)->slug('_')],
                [
                    'name' => $specialty,
                    'status' => 'reference',
                    'config' => json_encode(['specialty' => $specialty]),
                ]
            );
        }
    }
}
