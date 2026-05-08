<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\AuditLog;
use Illuminate\Http\Request;

trait RecordsAuditLogs
{
    protected function recordAudit(Request $request, string $action, string $entityType, ?int $entityId = null, array $metadata = []): void
    {
        AuditLog::create([
            'user_id' => optional($request->user())->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'created_at' => now(),
            'metadata' => $metadata,
        ]);
    }
}
