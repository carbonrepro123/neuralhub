<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportExtraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'patient_id',
        'doctor_id',
        'ocr_text',
        'summary',
        'vitals_json',
        'abnormal_findings_json',
        'review_status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'vitals_json' => 'array',
        'abnormal_findings_json' => 'array',
        'approved_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(PatientDocument::class, 'document_id');
    }
}
