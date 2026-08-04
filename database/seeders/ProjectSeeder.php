<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;
use App\Models\ProjectMember;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schema;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('project_members')->delete();
        DB::table('projects')->delete();
        Schema::enableForeignKeyConstraints();

        $nhv = User::where('username', 'nhv')->orWhere('name', 'LIKE', '%Hoàng Việt%')->first();
        $tqm = User::where('username', 'tqm')->orWhere('name', 'LIKE', '%Quang Minh%')->first();
        $ntk = User::where('username', 'ntk')->orWhere('name', 'LIKE', '%Trung Kiên%')->first();

        $leadId = $tqm ? $tqm->id : ($nhv ? $nhv->id : 1);

        // Project 1: W-LALOT
        $p1 = Project::create([
            'name' => '[WIFI Marketing] Lalot',
            'code' => 'W-LALOT',
            'weamis_fund_percentage' => 10.00,
            'lead_user_id' => $leadId,
            'created_by_user_id' => $leadId,
            'status' => 'active',
            'description' => 'Không có mô tả dự án',
        ]);
        if ($nhv) ProjectMember::create(['project_id' => $p1->id, 'user_id' => $nhv->id, 'share_percentage' => 10.00]);
        if ($ntk) ProjectMember::create(['project_id' => $p1->id, 'user_id' => $ntk->id, 'share_percentage' => 40.00]);
        if ($tqm) ProjectMember::create(['project_id' => $p1->id, 'user_id' => $tqm->id, 'share_percentage' => 40.00]);

        // Project 2: W-BMG
        $p2 = Project::create([
            'name' => '[WIFI Marketing] Bánh Mì Gác',
            'code' => 'W-BMG',
            'weamis_fund_percentage' => 10.00,
            'lead_user_id' => $leadId,
            'created_by_user_id' => $leadId,
            'status' => 'active',
            'description' => 'Không có mô tả dự án',
        ]);
        if ($nhv) ProjectMember::create(['project_id' => $p2->id, 'user_id' => $nhv->id, 'share_percentage' => 10.00]);
        if ($ntk) ProjectMember::create(['project_id' => $p2->id, 'user_id' => $ntk->id, 'share_percentage' => 40.00]);
        if ($tqm) ProjectMember::create(['project_id' => $p2->id, 'user_id' => $tqm->id, 'share_percentage' => 40.00]);

        // Project 3: W-EB
        $p3 = Project::create([
            'name' => '[WIFI Marketing] Everbloom',
            'code' => 'W-EB',
            'weamis_fund_percentage' => 10.00,
            'lead_user_id' => $leadId,
            'created_by_user_id' => $leadId,
            'status' => 'active',
            'description' => 'Không có mô tả dự án',
        ]);
        if ($nhv) ProjectMember::create(['project_id' => $p3->id, 'user_id' => $nhv->id, 'share_percentage' => 10.00]);
        if ($ntk) ProjectMember::create(['project_id' => $p3->id, 'user_id' => $ntk->id, 'share_percentage' => 40.00]);
        if ($tqm) ProjectMember::create(['project_id' => $p3->id, 'user_id' => $tqm->id, 'share_percentage' => 40.00]);

        // Project 4: BMG
        $p4 = Project::create([
            'name' => '[Landing Page] Bánh Mì Gác',
            'code' => 'BMG',
            'weamis_fund_percentage' => 10.00,
            'lead_user_id' => $leadId,
            'created_by_user_id' => $leadId,
            'status' => 'active',
            'description' => 'Không có mô tả dự án',
        ]);
        if ($ntk) ProjectMember::create(['project_id' => $p4->id, 'user_id' => $ntk->id, 'share_percentage' => 40.00]);
        if ($tqm) ProjectMember::create(['project_id' => $p4->id, 'user_id' => $tqm->id, 'share_percentage' => 50.00]);
    }
}
