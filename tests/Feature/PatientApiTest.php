<?php

namespace Tests\Feature;

use Tests\TestCase;

class PatientApiTest extends TestCase
{
    public function test_patients_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/patients');

        $response->assertStatus(401);
    }
}
