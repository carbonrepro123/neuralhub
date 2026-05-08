<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RecordsAuditLogs;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use RecordsAuditLogs;

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            Notification::where('user_id', $request->user()->id)->latest()->paginate()
        );
    }

    public function markRead(Request $request, Notification $notification): JsonResponse
    {
        $notification->update(['read_at' => now()]);
        $this->recordAudit($request, 'read_notification', 'notifications', $notification->id);

        return response()->json($notification);
    }
}
