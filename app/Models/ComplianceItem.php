<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplianceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'certification_type',
        'issuing_authority',
        'license_number',
        'state',
        'issue_date',
        'expiry_date',
        'renewal_frequency',
        'document_upload',
        'status',
        'reminder_dates',
        'notes',
        'last_verified_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'reminder_dates' => 'array',
        'last_verified_at' => 'datetime',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(ComplianceReminder::class);
    }
}
