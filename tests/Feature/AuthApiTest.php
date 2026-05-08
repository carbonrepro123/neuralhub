<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthApiTest extends TestCase
{
    public function test_register_endpoint_exists(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'role' => 'clinic_admin',
            'clinic_name' => 'North Clinic',
        ]);

        $response->assertStatus(201);
    }
}
