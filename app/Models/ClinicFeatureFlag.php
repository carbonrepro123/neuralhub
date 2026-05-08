<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicFeatureFlag extends Model
{
    use HasFactory;

    protected $table = 'clinic_feature_flags';

    protected $fillable = [
        'clinic_id',
        'feature_key',
        'feature_name',
        'enabled',
        'settings',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'settings' => 'array',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }
}
