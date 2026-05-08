<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIOutput extends Model
{
    use HasFactory;

    protected $table = 'ai_outputs';

    protected $fillable = [
        'task_id',
        'patient_id',
        'doctor_id',
        'agent_type',
        'input_text',
        'output_json',
        'status',
        'reviewed_by_doctor',
        'confidence_score',
        'disclaimer',
        'doctor_review_status',
    ];

    protected $casts = [
        'output_json' => 'array',
        'reviewed_by_doctor' => 'boolean',
        'confidence_score' => 'float',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(AIAgentTask::class, 'task_id');
    }
}
