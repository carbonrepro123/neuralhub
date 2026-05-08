<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'compliance_item_id',
        'uploaded_by',
        'original_name',
        'path',
        'disk',
        'status',
    ];

    public function complianceItem(): BelongsTo
    {
        return $this->belongsTo(ComplianceItem::class);
    }
}
