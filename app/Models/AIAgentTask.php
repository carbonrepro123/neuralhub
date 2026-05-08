<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AIAgentTask extends Model
{
    use HasFactory;

    protected $table = 'ai_agent_tasks';

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_id',
        'agent_type',
        'input_text',
        'input_json',
        'status',
        'priority',
    ];

    protected $casts = [
        'input_json' => 'array',
    ];

    public function outputs(): HasMany
    {
        return $this->hasMany(AIOutput::class, 'task_id');
    }
}
