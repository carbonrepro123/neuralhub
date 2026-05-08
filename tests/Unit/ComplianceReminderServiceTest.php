<?php

namespace Tests\Unit;

use App\Services\Compliance\ComplianceReminderService;
use Tests\TestCase;

class ComplianceReminderServiceTest extends TestCase
{
    public function test_service_can_be_resolved(): void
    {
        $service = app(ComplianceReminderService::class);

        $this->assertInstanceOf(ComplianceReminderService::class, $service);
    }
}
