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
        // Safely clear old project tables
        DB::statement('PRAGMA foreign_keys = OFF;');
        if (Schema::hasTable('project_members')) {
            ProjectMember::truncate();
        }
        if (Schema::hasTable('projects')) {
            Project::truncate();
        }
        DB::statement('PRAGMA foreign_keys = ON;');

        $nhv = User::where('username', 'nhv')->first();
        $tqm = User::where('username', 'tqm')->first();
        $ntk = User::where('username', 'ntk')->orWhere('name', 'LIKE', '%Trung Kiên%')->first();

        // 1. [WIFI Marketing] Lalot
        $p1 = Project::create([
            'name' => '[WIFI Marketing] Lalot',
            'code' => 'W-LALOT',
            'weamis_fund_percentage' => 10.00,
            'lead_user_id' => $tqm ? $tqm->id : 9,
            'status' => 'active',
            'description' => 'Không có mô tả dự án',
        ]);
        if ($nhv) ProjectMember::create(['project_id' => $p1->id, 'user_id' => $nhv->id, 'share_percentage' => 10.00]);
        if ($ntk) ProjectMember::create(['project_id' => $p1->id, 'user_id' => $ntk->id, 'share_percentage' => 40.00]);
        if ($tqm) ProjectMember::create(['project_id' => $p1->id, 'user_id' => $tqm->id, 'share_percentage' => 40.00]);

        // 2. [WIFI Marketing] Bánh Mì Gác
        $p2 = Project::create([
            'name' => '[WIFI Marketing] Bánh Mì Gác',
            'code' => 'W-BMG',
            'weamis_fund_percentage' => 10.00,
            'lead_user_id' => $tqm ? $tqm->id : 9,
            'status' => 'active',
            'description' => 'Không có mô tả dự án',
        ]);
        if ($nhv) ProjectMember::create(['project_id' => $p2->id, 'user_id' => $nhv->id, 'share_percentage' => 10.00]);
        if ($ntk) ProjectMember::create(['project_id' => $p2->id, 'user_id' => $ntk->id, 'share_percentage' => 40.00]);
        if ($tqm) ProjectMember::create(['project_id' => $p2->id, 'user_id' => $tqm->id, 'share_percentage' => 40.00]);

        // 3. [WIFI Marketing] Everbloom
        $p3 = Project::create([
            'name' => '[WIFI Marketing] Everbloom',
            'code' => 'W-EB',
            'weamis_fund_percentage' => 10.00,
            'lead_user_id' => $tqm ? $tqm->id : 9,
            'status' => 'active',
            'description' => 'Không có mô tả dự án',
        ]);
        if ($nhv) ProjectMember::create(['project_id' => $p3->id, 'user_id' => $nhv->id, 'share_percentage' => 10.00]);
        if ($ntk) ProjectMember::create(['project_id' => $p3->id, 'user_id' => $ntk->id, 'share_percentage' => 40.00]);
        if ($tqm) ProjectMember::create(['project_id' => $p3->id, 'user_id' => $tqm->id, 'share_percentage' => 40.00]);

        // 4. [Landing Page] Bánh Mì Gác
        $p4 = Project::create([
            'name' => '[Landing Page] Bánh Mì Gác',
            'code' => 'BMG',
            'weamis_fund_percentage' => 10.00,
            'lead_user_id' => $tqm ? $tqm->id : 9,
            'status' => 'active',
            'description' => 'Không có mô tả dự án',
        ]);
        if ($ntk) ProjectMember::create(['project_id' => $p4->id, 'user_id' => $ntk->id, 'share_percentage' => 40.00]);
        if ($tqm) ProjectMember::create(['project_id' => $p4->id, 'user_id' => $tqm->id, 'share_percentage' => 50.00]);
    }
}
