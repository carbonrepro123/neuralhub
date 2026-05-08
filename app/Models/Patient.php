<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'user_id',
        'primary_doctor_id',
        'name',
        'date_of_birth',
        'gender',
        'phone',
        'email',
        'address',
        'emergency_contact',
        'insurance_provider',
        'insurance_member_id',
        'medical_history',
        'allergies',
        'medications',
        'conditions',
        'notes',
        'consent_status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'medical_history' => 'array',
        'allergies' => 'array',
        'medications' => 'array',
        'conditions' => 'array',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'primary_doctor_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PatientDocument::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
