<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AIAgent extends Model
{
    use HasFactory;

    protected $table = 'ai_agents';

    protected $fillable = [
        'name',
        'agent_type',
        'status',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'doctor_agent_assignments', 'ai_agent_id', 'doctor_id')
            ->withPivot(['assigned_by', 'status', 'configuration'])
            ->withTimestamps();
    }
}
