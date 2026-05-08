<?php

namespace App\Support;

use App\Models\User;

class Portal
{
    public static function roleDashboardRoute(User $user): string
    {
        return match ($user->role) {
            'super_admin' => 'portal.super-admin',
            'clinic_admin' => 'portal.clinic-admin',
            'patient' => 'portal.patient',
            default => 'dashboard',
        };
    }
}
