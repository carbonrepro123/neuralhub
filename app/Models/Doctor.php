<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'clinic_id',
        'specialty',
        'license_number',
        'state',
        'npi_number',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'primary_doctor_id');
    }

    public function complianceItems(): HasMany
    {
        return $this->hasMany(ComplianceItem::class);
    }

    public function agentAssignments(): HasMany
    {
        return $this->hasMany(DoctorAgentAssignment::class);
    }

    public function aiAgents(): BelongsToMany
    {
        return $this->belongsToMany(AIAgent::class, 'doctor_agent_assignments')
            ->withPivot(['assigned_by', 'status', 'configuration'])
            ->withTimestamps();
    }
}
