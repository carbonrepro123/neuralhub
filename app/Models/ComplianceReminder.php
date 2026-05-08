<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'compliance_item_id',
        'channel',
        'send_at',
        'sent_at',
        'status',
        'payload',
    ];

    protected $casts = [
        'send_at' => 'datetime',
        'sent_at' => 'datetime',
        'payload' => 'array',
    ];

    public function complianceItem(): BelongsTo
    {
        return $this->belongsTo(ComplianceItem::class);
    }
}
