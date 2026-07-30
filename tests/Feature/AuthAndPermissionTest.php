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
            'role' => 'member',
        ]);

        $response = $this->post('/login', [
            'login' => 'nhv',
            'password' => '1234',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_access_member_management_and_create_member()
    {
        $admin = User::create([
            'name' => 'Quản Trị Viên',
            'username' => 'admin',
            'email' => 'admin@weamis.com',
            'password' => Hash::make('1322'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/members');
        $response->assertStatus(200);
        $response->assertSee('Quản Lý Tài Khoản Thành Viên');

        $responseCreate = $this->actingAs($admin)->post('/members', [
            'name' => 'Trần Văn Nam',
            'username' => 'tvn',
            'email' => 'nam@weamis.com',
            'password' => '1234',
            'role' => 'member',
            'share_percentage' => 10,
        ]);

        $responseCreate->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['username' => 'tvn', 'name' => 'Trần Văn Nam']);
    }

    public function test_non_admin_cannot_access_member_management()
    {
        $member = User::create([
            'name' => 'Ordinary Member',
            'username' => 'nhv',
            'email' => 'member@weamis.com',
            'password' => Hash::make('1234'),
            'role' => 'member',
        ]);

        $response = $this->actingAs($member)->get('/members');
        $response->assertRedirect('/');
        $response->assertSessionHas('error');
    }

    public function test_non_lead_cannot_delete_or_update_project()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin_test',
            'email' => 'admin@weamis.com',
            'password' => Hash::make('1322'),
            'role' => 'admin',
        ]);

        $lead = User::create([
            'name' => 'Project Lead',
            'username' => 'lead_test',
            'email' => 'lead@weamis.com',
            'password' => Hash::make('1234'),
            'role' => 'member',
        ]);

        $member = User::create([
            'name' => 'Ordinary Member',
            'username' => 'member_test',
            'email' => 'member@weamis.com',
            'password' => Hash::make('1234'),
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
