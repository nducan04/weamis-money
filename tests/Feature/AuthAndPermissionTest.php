<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Models\User;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAndPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Fund::create([
            'id' => 1,
            'name' => 'Trả nợ thuê Ltd',
            'balance' => 10000000.00,
            'total_profit' => 500000.00,
        ]);
    }

    public function test_unauthenticated_user_is_redirected_to_login()
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_user_can_login_with_username_and_password()
    {
        $user = User::create([
            'name' => 'Nguyễn Hoàng Việt',
            'username' => 'nhv',
            'email' => 'viet@weamis.com',
            'password' => Hash::make('1234'),
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'login' => 'nhv',
            'password' => '1234',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_register_new_account()
    {
        $response = $this->post('/register', [
            'name' => 'Trần Văn Nam',
            'username' => 'tvn',
            'email' => 'nam@weamis.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', ['username' => 'tvn', 'name' => 'Trần Văn Nam']);
        $this->assertAuthenticated();
    }

    public function test_non_lead_cannot_delete_or_update_project()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@weamis.com',
            'password' => Hash::make('weamis123'),
            'role' => 'admin',
        ]);

        $lead = User::create([
            'name' => 'Project Lead',
            'email' => 'lead@weamis.com',
            'password' => Hash::make('weamis123'),
            'role' => 'lead',
        ]);

        $member = User::create([
            'name' => 'Ordinary Member',
            'email' => 'member@weamis.com',
            'password' => Hash::make('weamis123'),
            'role' => 'member',
        ]);

        $project = Project::create([
            'name' => 'Secret Project',
            'code' => 'SPC',
            'weamis_fund_percentage' => 10,
            'lead_user_id' => $lead->id,
            'created_by_user_id' => $lead->id,
        ]);

        // Member attempts to update project
        $response = $this->actingAs($member)->put('/projects/' . $project->id, [
            'name' => 'Hacked Name',
            'status' => 'active',
            'weamis_fund_percentage' => 5,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals('Secret Project', $project->fresh()->name);

        // Member attempts to delete project
        $responseDelete = $this->actingAs($member)->delete('/projects/' . $project->id);
        $responseDelete->assertSessionHas('error');
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }
}
