<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        // Clear old dummy projects safely
        if (Schema::hasTable('project_user')) {
            DB::table('project_user')->delete();
        }
        if (Schema::hasTable('projects')) {
            Project::query()->delete();
        }

        $nhv = User::where('username', 'nhv')->first();
        $tqm = User::where('username', 'tqm')->first();
        $ntk = User::where('username', 'ntk')->orWhere('name', 'LIKE', '%Trung Kiên%')->first();

        // 1. Wifi Marketing
        $p1 = Project::create([
            'name' => 'Wifi Marketing',
            'code' => 'WIFI',
            'weamis_fund_percentage' => 10.00,
            'lead_user_id' => $tqm ? $tqm->id : 9,
            'status' => 'active',
            'description' => 'BMG + EB + BC + LALOT',
        ]);

        if ($nhv) $p1->members()->attach($nhv->id, ['share_percentage' => 10.00]);
        if ($ntk) $p1->members()->attach($ntk->id, ['share_percentage' => 40.00]);
        if ($tqm) $p1->members()->attach($tqm->id, ['share_percentage' => 40.00]);

        // 2. Bánh Mì Gác
        $p2 = Project::create([
            'name' => 'Bánh Mì Gác',
            'code' => 'BMG',
            'weamis_fund_percentage' => 10.00,
            'lead_user_id' => $tqm ? $tqm->id : 9,
            'status' => 'active',
            'description' => 'Không có mô tả dự án',
        ]);

        if ($ntk) $p2->members()->attach($ntk->id, ['share_percentage' => 40.00]);
        if ($tqm) $p2->members()->attach($tqm->id, ['share_percentage' => 50.00]);
    }
}
