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
        $nda = User::where('username', 'nda')->orWhere('name', 'LIKE', '%Đức An%')->first();
        $nhv = User::where('username', 'nhv')->orWhere('name', 'LIKE', '%Hoàng Việt%')->first();
        $tqm = User::where('username', 'tqm')->orWhere('name', 'LIKE', '%Quang Minh%')->first();
        $ntk = User::where('username', 'ntk')->orWhere('name', 'LIKE', '%Trung Kiên%')->first();

        $leadId = $tqm ? $tqm->id : ($nhv ? $nhv->id : 1);

        // Project 0: WM (Weamis Money)
        $wmLeadId = $nda ? $nda->id : $leadId;
        $p0 = Project::firstOrCreate(
            ['code' => 'WM'],
            [
                'name' => 'Weamis Money',
                'description' => 'Quản lý tiền Weamis',
                'release_date' => '2026-07-31',
                'weamis_fund_percentage' => 0.00,
                'lead_user_id' => $wmLeadId,
                'created_by_user_id' => $wmLeadId,
                'status' => 'active',
            ]
        );
        if ($nda) ProjectMember::firstOrCreate(['project_id' => $p0->id, 'user_id' => $nda->id], ['share_percentage' => 100.00]);

        // Project 1: W-LALOT
        $p1 = Project::firstOrCreate(
            ['code' => 'W-LALOT'],
            [
                'name' => '[WIFI Marketing] Lalot',
                'weamis_fund_percentage' => 10.00,
                'lead_user_id' => $leadId,
                'created_by_user_id' => $leadId,
                'status' => 'active',
                'description' => 'Không có mô tả dự án',
            ]
        );
        if ($nhv) ProjectMember::firstOrCreate(['project_id' => $p1->id, 'user_id' => $nhv->id], ['share_percentage' => 10.00]);
        if ($ntk) ProjectMember::firstOrCreate(['project_id' => $p1->id, 'user_id' => $ntk->id], ['share_percentage' => 40.00]);
        if ($tqm) ProjectMember::firstOrCreate(['project_id' => $p1->id, 'user_id' => $tqm->id], ['share_percentage' => 40.00]);

        // Project 2: W-BMG
        $p2 = Project::firstOrCreate(
            ['code' => 'W-BMG'],
            [
                'name' => '[WIFI Marketing] Bánh Mì Gác',
                'weamis_fund_percentage' => 10.00,
                'lead_user_id' => $leadId,
                'created_by_user_id' => $leadId,
                'status' => 'active',
                'description' => 'Không có mô tả dự án',
            ]
        );
        if ($nhv) ProjectMember::firstOrCreate(['project_id' => $p2->id, 'user_id' => $nhv->id], ['share_percentage' => 10.00]);
        if ($ntk) ProjectMember::firstOrCreate(['project_id' => $p2->id, 'user_id' => $ntk->id], ['share_percentage' => 40.00]);
        if ($tqm) ProjectMember::firstOrCreate(['project_id' => $p2->id, 'user_id' => $tqm->id], ['share_percentage' => 40.00]);

        // Project 3: W-EB
        $p3 = Project::firstOrCreate(
            ['code' => 'W-EB'],
            [
                'name' => '[WIFI Marketing] Everbloom',
                'weamis_fund_percentage' => 10.00,
                'lead_user_id' => $leadId,
                'created_by_user_id' => $leadId,
                'status' => 'active',
                'description' => 'Không có mô tả dự án',
            ]
        );
        if ($nhv) ProjectMember::firstOrCreate(['project_id' => $p3->id, 'user_id' => $nhv->id], ['share_percentage' => 10.00]);
        if ($ntk) ProjectMember::firstOrCreate(['project_id' => $p3->id, 'user_id' => $ntk->id], ['share_percentage' => 40.00]);
        if ($tqm) ProjectMember::firstOrCreate(['project_id' => $p3->id, 'user_id' => $tqm->id], ['share_percentage' => 40.00]);

        // Project 4: BMG
        $p4 = Project::firstOrCreate(
            ['code' => 'BMG'],
            [
                'name' => '[Landing Page] Bánh Mì Gác',
                'weamis_fund_percentage' => 10.00,
                'lead_user_id' => $leadId,
                'created_by_user_id' => $leadId,
                'status' => 'active',
                'description' => 'Không có mô tả dự án',
            ]
        );
        if ($ntk) ProjectMember::firstOrCreate(['project_id' => $p4->id, 'user_id' => $ntk->id], ['share_percentage' => 40.00]);
        if ($tqm) ProjectMember::firstOrCreate(['project_id' => $p4->id, 'user_id' => $tqm->id], ['share_percentage' => 50.00]);

        // Project 5: EVB ([Landing Page] Everbloom)
        $son = User::where('username', 'sonht')->orWhere('name', 'LIKE', '%Trùng Sơn%')->first();
        $p5 = Project::firstOrCreate(
            ['code' => 'EVB'],
            [
                'name' => '[Landing Page] Everbloom',
                'description' => 'Dự án Everbloom 5 triệu',
                'weamis_fund_percentage' => 10.00,
                'lead_user_id' => $son ? $son->id : $leadId,
                'created_by_user_id' => $leadId,
                'status' => 'active',
            ]
        );
        if ($nhv) ProjectMember::firstOrCreate(['project_id' => $p5->id, 'user_id' => $nhv->id], ['share_percentage' => 45.00]);
        if ($son) ProjectMember::firstOrCreate(['project_id' => $p5->id, 'user_id' => $son->id], ['share_percentage' => 45.00]);

        // Attach 3.25M transactions to projects
        $txLalot = \App\Models\Transaction::where('description', 'like', '%Wifi lalot%')->where('amount', 3250000)->first();
        if ($txLalot) {
            $txLalot->project_id = $p1->id;
            $txLalot->save();
        }

        $txEb = \App\Models\Transaction::where('amount', 3250000)
            ->where('description', 'Góp vào quỹ chung')
            ->first();
        if ($txEb) {
            $txEb->project_id = $p3->id;
            $txEb->save();
        }

        // Attach 5M transaction to EVB
        $tx5m = \App\Models\Transaction::where('amount', 5000000)->where('description', 'like', '%Everbloom%')->first();
        if ($tx5m) {
            $tx5m->project_id = $p5->id;
            $tx5m->save();
        }
    }
}
