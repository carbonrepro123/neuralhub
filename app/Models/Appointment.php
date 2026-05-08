<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'doctor_id',
        'patient_id',
        'appointment_type',
        'appointment_date',
        'start_time',
        'end_time',
        'reason_for_visit',
        'status',
        'daily_room_url',
        'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function videoRoom(): HasOne
    {
        return $this->hasOne(VideoRoom::class);
    }

    public function soapNotes(): HasMany
    {
        return $this->hasMany(SoapNote::class);
    }
}
