<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed();
        $user = \App\Models\User::first();
        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
    }
}
