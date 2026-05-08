<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplianceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['type' => 'State Medical License', 'renewal_frequency' => '1-2 years depending state'],
            ['type' => 'CME', 'renewal_frequency' => 'annual'],
            ['type' => 'DEA License', 'renewal_frequency' => '3 years'],
            ['type' => 'Board Certification', 'renewal_frequency' => '7-10 years'],
            ['type' => 'CAQH Credentialing', 'renewal_frequency' => '120 days'],
            ['type' => 'Hospital Privileges', 'renewal_frequency' => '1-2 years'],
            ['type' => 'Malpractice Insurance', 'renewal_frequency' => 'annual'],
            ['type' => 'ACLS', 'renewal_frequency' => '2 years'],
            ['type' => 'BLS', 'renewal_frequency' => '2 years'],
            ['type' => 'PALS', 'renewal_frequency' => '2 years'],
            ['type' => 'ATLS', 'renewal_frequency' => '4 years'],
        ];

        foreach ($types as $type) {
            DB::table('ai_agents')->updateOrInsert(
                ['agent_type' => 'compliance_reference_' . str($type['type'])->slug('_')],
                ['name' => $type['type'], 'status' => 'reference', 'config' => json_encode($type)]
            );
        }
    }
}
