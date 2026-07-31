<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        if (Project::count() === 0) {
            $nhv = User::where('username', 'nhv')->first();
            $tqm = User::where('username', 'tqm')->first();
            $son = User::where('username', 'htson')->orWhere('avatar', 'TS')->first();

            $p1 = Project::create([
                'name' => 'Lắp Đặt Hạ Tầng Wifi',
                'code' => 'WIFI-01',
                'weamis_fund_percentage' => 10.00,
                'lead_user_id' => $nhv ? $nhv->id : 2,
                'status' => 'active',
                'description' => 'Thi công lắp đặt và bảo trì hạ tầng hệ thống Wifi',
            ]);

            if ($nhv) $p1->members()->attach($nhv->id, ['share_percentage' => 50.00]);
            if ($tqm) $p1->members()->attach($tqm->id, ['share_percentage' => 50.00]);

            $p2 = Project::create([
                'name' => 'Hệ Thống Mạng BMG',
                'code' => 'BMG-02',
                'weamis_fund_percentage' => 15.00,
                'lead_user_id' => $nhv ? $nhv->id : 2,
                'status' => 'active',
                'description' => 'Triển khai dịch vụ truyền thông và hạ tầng BMG Network',
            ]);

            if ($nhv) $p2->members()->attach($nhv->id, ['share_percentage' => 40.00]);
            if ($son) $p2->members()->attach($son->id, ['share_percentage' => 60.00]);
        }
    }
}
