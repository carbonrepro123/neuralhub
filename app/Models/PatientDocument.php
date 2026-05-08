<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'uploaded_by',
        'document_type',
        'original_name',
        'disk',
        'path',
        'mime_type',
        'size',
        'encryption_key_ref',
        'status',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function reportExtractions(): HasMany
    {
        return $this->hasMany(ReportExtraction::class, 'document_id');
    }
}
