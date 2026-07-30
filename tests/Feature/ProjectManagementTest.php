<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
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

        $admin = User::create([
            'id' => 1,
            'name' => 'Nguyễn Hoàng Việt',
            'email' => 'viet@weamis.com',
            'role' => 'admin',
            'share_percentage' => 45.0,
            'current_debt' => 0.0,
        ]);

        User::create([
            'id' => 2,
            'name' => 'Hồ Trung Sơn',
            'email' => 'son@weamis.com',
            'role' => 'lead',
            'share_percentage' => 45.0,
            'current_debt' => 0.0,
        ]);

        User::create([
            'id' => 3,
            'name' => 'Weamis Fund',
            'email' => 'fund@weamis.com',
            'role' => 'member',
            'share_percentage' => 10.0,
            'current_debt' => 0.0,
        ]);

        $this->actingAs($admin);
    }

    public function test_projects_page_renders_successfully()
    {
        $response = $this->get('/projects');
        $response->assertStatus(200);
        $response->assertSee('Quản Lý Dự Án');
    }

    public function test_can_create_project_with_member_shares()
    {
        $response = $this->post('/projects', [
            'name' => 'Everbloom',
            'code' => 'EVB',
            'description' => 'Dự án Everbloom 5 triệu',
            'weamis_fund_percentage' => 10.0,
            'lead_user_id' => 2,
            'members' => [
                ['user_id' => 1, 'share_percentage' => 45.0],
                ['user_id' => 2, 'share_percentage' => 45.0],
            ]
        ]);

        $response->assertRedirect('/projects');
        $this->assertDatabaseHas('projects', [
            'name' => 'Everbloom',
            'code' => 'EVB',
            'weamis_fund_percentage' => 10.0,
        ]);

        $project = Project::where('code', 'EVB')->first();
        $this->assertEquals(2, $project->members()->count());
    }

    public function test_project_show_page_and_audit()
    {
        $project = Project::create([
            'name' => 'VMU Engineer',
            'code' => 'VMU',
            'weamis_fund_percentage' => 10.0,
            'status' => 'active',
        ]);

        $response = $this->get('/projects/' . $project->id);
        $response->assertStatus(200);
        $response->assertSee('VMU Engineer');
    }

    public function test_analytics_networth_page_renders()
    {
        $response = $this->get('/analytics/networth');
        $response->assertStatus(200);
        $response->assertSee('Tài Sản Ròng');
        $response->assertSee('Collaboration Graph');
    }

    public function test_project_completion_credits_weamis_fund()
    {
        $project = Project::create([
            'name' => 'Everbloom Sales',
            'code' => 'EBS',
            'weamis_fund_percentage' => 10.0,
            'status' => 'active',
            'lead_user_id' => 2,
        ]);

        Transaction::create([
            'fund_id' => 1,
            'project_id' => $project->id,
            'user_id' => 2,
            'type' => 'contribution',
            'amount' => 5000000.00,
            'description' => 'Doanh thu Everbloom',
            'status' => 'approved',
        ]);

        $response = $this->put('/projects/' . $project->id, [
            'name' => 'Everbloom Sales',
            'description' => 'Hoàn thành',
            'weamis_fund_percentage' => 10.0,
            'lead_user_id' => 2,
            'status' => 'completed',
        ]);

        $response->assertRedirect('/projects/' . $project->id);

        $fund = Fund::find(1);
        $this->assertEquals(10500000.00, $fund->balance);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'completed',
            'fund_credited_amount' => 500000.00,
        ]);
    }
}
