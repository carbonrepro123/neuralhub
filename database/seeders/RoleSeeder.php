<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super_admin', 'description' => 'Full platform access'],
            ['name' => 'Clinic Admin', 'slug' => 'clinic_admin', 'description' => 'Clinic operations and user management'],
            ['name' => 'Doctor', 'slug' => 'doctor', 'description' => 'Patient care, calls, AI review, compliance'],
            ['name' => 'Staff', 'slug' => 'staff', 'description' => 'Appointment and intake support'],
            ['name' => 'Patient', 'slug' => 'patient', 'description' => 'Portal access for appointments and documents'],
        ];

        foreach ($roles as $role) {
            $roleRecord = Role::firstOrCreate(
                ['slug' => $role['slug']],
                $role
            );

            User::firstOrCreate(
                ['email' => "{$role['slug']}@clinixai.test"],
                [
                    'name' => $role['name'],
                    'password' => Hash::make('Password123!'),
                    'role' => $role['slug'],
                    'role_id' => $roleRecord->id,
                    'status' => 'active',
                ]
            );
        }
    }
}
