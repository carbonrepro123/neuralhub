<?php

namespace App\Services\Compliance;

use App\Models\ComplianceItem;
use App\Models\ComplianceReminder;
use Carbon\Carbon;

class ComplianceReminderService
{
    public function seedDefaultReminders(ComplianceItem $item): void
    {
        $days = [90, 60, 30, 7, 0];

        foreach ($days as $day) {
            ComplianceReminder::create([
                'compliance_item_id' => $item->id,
                'channel' => 'email',
                'send_at' => Carbon::parse($item->expiry_date)->subDays($day),
                'status' => 'scheduled',
                'payload' => [
                    'days_before_expiry' => $day,
                    'message' => $day === 0
                        ? 'Certification expired. Doctor review required.'
                        : "Certification expires in {$day} days. Doctor review required.",
                ],
            ]);
        }
    }
}
