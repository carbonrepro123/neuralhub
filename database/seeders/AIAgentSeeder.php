<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AIAgentSeeder extends Seeder
{
    public function run(): void
    {
        $agents = [
            ['name' => 'AI Receptionist Agent', 'agent_type' => 'receptionist_agent'],
            ['name' => 'AI Intake Agent', 'agent_type' => 'intake_agent'],
            ['name' => 'AI Report Reader Agent', 'agent_type' => 'report_reader'],
            ['name' => 'AI Vitals Agent', 'agent_type' => 'vitals_agent'],
            ['name' => 'AI Abnormal Value Agent', 'agent_type' => 'abnormal_value_agent'],
            ['name' => 'AI Scribe Agent', 'agent_type' => 'scribe_agent'],
            ['name' => 'AI Follow-up Agent', 'agent_type' => 'follow_up_agent'],
            ['name' => 'AI Compliance Agent', 'agent_type' => 'compliance_agent'],
        ];

        foreach ($agents as $agent) {
            DB::table('ai_agents')->updateOrInsert(
                ['agent_type' => $agent['agent_type']],
                [
                    'name' => $agent['name'],
                    'status' => 'active',
                    'config' => json_encode([
                        'review_required' => true,
                        'disclaimer' => 'AI assistance only. Doctor review required.',
                    ]),
                ]
            );
        }
    }
}
