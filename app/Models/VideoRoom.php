<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'provider',
        'external_room_id',
        'room_url',
        'doctor_token',
        'patient_token',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function getDoctorJoinUrlAttribute(): ?string
    {
        return $this->room_url;
    }

    public function getPatientJoinUrlAttribute(): ?string
    {
        return $this->room_url;
    }
}
