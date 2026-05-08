<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorAgentAssignment extends Model
{
    use HasFactory;

    protected $table = 'doctor_agent_assignments';

    protected $fillable = [
        'doctor_id',
        'ai_agent_id',
        'assigned_by',
        'status',
        'configuration',
    ];

    protected $casts = [
        'configuration' => 'array',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function aiAgent(): BelongsTo
    {
        return $this->belongsTo(AIAgent::class, 'ai_agent_id');
    }
}
