<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin',
            'password' => bcrypt('1322'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin',
            'password' => '1322',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/');
    }

    public function test_user_cannot_login_with_incorrect_password(): void
    {
        $user = User::factory()->create([
            'email' => 'admin',
            'password' => bcrypt('1322'),
        ]);

        $this->post('/login', [
            'email' => 'admin',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_new_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Nguyễn Văn Mới',
            'email' => 'newuser@weamis.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@weamis.com',
            'name' => 'Nguyễn Văn Mới',
        ]);
        $response->assertRedirect('/');
    }

    public function test_logged_in_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_user_can_request_password_reset(): void
    {
        $user = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@weamis.com',
        ]);

        $response = $this->post('/forgot-password', [
            'account' => 'admin',
        ]);

        $response->assertRedirect('/reset-password');
        $this->assertEquals($user->id, session('reset_user_id'));
    }

    public function test_user_can_reset_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('oldpassword'),
        ]);

        $response = $this->withSession(['reset_user_id' => $user->id])
            ->post('/reset-password', [
                'user_id' => $user->id,
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect('/login');
        
        // Verify new password works
        $loginResponse = $this->post('/login', [
            'email' => $user->email,
            'password' => 'newpassword123',
        ]);
        
        $this->assertAuthenticatedAs($user);
    }
}
