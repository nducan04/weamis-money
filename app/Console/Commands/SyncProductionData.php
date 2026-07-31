<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SyncProductionData extends Command
{
    protected $signature = 'db:sync {--url=https://money.vietnh.io.vn} {--key=}';
    protected $description = 'Sync real production database to localhost environment';

    public function handle()
    {
        $baseUrl = rtrim($this->option('url'), '/');
        $key = $this->option('key') ?: env('DB_SYNC_KEY', 'weamis_money_secret_sync_key_2026');

        $endpoint = "{$baseUrl}/api/db-dump?key={$key}";
        $this->info("🔄 Đang kết nối và tải dữ liệu từ Production: {$baseUrl} ...");

        try {
            $response = Http::withoutVerifying()->timeout(15)->get($endpoint);

            if ($response->failed()) {
                $this->error("❌ Không thể kết nối tới Production (HTTP " . $response->status() . "). Kiểm tra lại đường dẫn URL hoặc DB_SYNC_KEY.");
                return 1;
            }

            $data = $response->json();

            if (isset($data['error'])) {
                $this->error("❌ Lỗi từ Production: " . $data['error']);
                return 1;
            }

            DB::transaction(function () use ($data) {
                // Disable Foreign Keys for SQLite
                DB::statement('PRAGMA foreign_keys = OFF;');

                // 1. Funds
                if (!empty($data['funds'])) {
                    DB::table('funds')->delete();
                    foreach ($data['funds'] as $row) {
                        DB::table('funds')->insert((array)$row);
                    }
                }

                // 2. Users
                if (!empty($data['users'])) {
                    DB::table('users')->delete();
                    foreach ($data['users'] as $row) {
                        DB::table('users')->insert((array)$row);
                    }
                }

                // 3. Projects
                if (!empty($data['projects'])) {
                    DB::table('projects')->delete();
                    foreach ($data['projects'] as $row) {
                        DB::table('projects')->insert((array)$row);
                    }
                }

                // 4. Project_Members
                if (!empty($data['project_members'])) {
                    DB::table('project_members')->delete();
                    foreach ($data['project_members'] as $row) {
                        DB::table('project_members')->insert((array)$row);
                    }
                }

                // 5. Transactions
                if (!empty($data['transactions'])) {
                    DB::table('transactions')->delete();
                    foreach ($data['transactions'] as $row) {
                        DB::table('transactions')->insert((array)$row);
                    }
                }

                // 6. Distributions
                if (!empty($data['distributions'])) {
                    DB::table('distributions')->delete();
                    foreach ($data['distributions'] as $row) {
                        DB::table('distributions')->insert((array)$row);
                    }
                }

                // 7. Seed Project Members locally since Production API doesn't export them (fallback)
                if (empty($data['project_members'])) {
                    DB::table('project_members')->delete();
                    $nhv = \App\Models\User::where('username', 'nhv')->orWhere('name', 'LIKE', '%Hoàng Việt%')->first();
                    $tqm = \App\Models\User::where('username', 'tqm')->orWhere('name', 'LIKE', '%Quang Minh%')->first();
                    $ntk = \App\Models\User::where('username', 'ntk')->orWhere('name', 'LIKE', '%Trung Kiên%')->first();
                    
                    $projects = \App\Models\Project::all();
                    foreach ($projects as $p) {
                        if (in_array($p->code, ['W-LALOT', 'W-BMG', 'W-EB'])) {
                            if ($nhv) \App\Models\ProjectMember::create(['project_id' => $p->id, 'user_id' => $nhv->id, 'share_percentage' => 10.00]);
                            if ($ntk) \App\Models\ProjectMember::create(['project_id' => $p->id, 'user_id' => $ntk->id, 'share_percentage' => 40.00]);
                            if ($tqm) \App\Models\ProjectMember::create(['project_id' => $p->id, 'user_id' => $tqm->id, 'share_percentage' => 40.00]);
                        } elseif ($p->code === 'BMG') {
                            if ($ntk) \App\Models\ProjectMember::create(['project_id' => $p->id, 'user_id' => $ntk->id, 'share_percentage' => 40.00]);
                            if ($tqm) \App\Models\ProjectMember::create(['project_id' => $p->id, 'user_id' => $tqm->id, 'share_percentage' => 50.00]);
                        }
                    }
                }

                DB::statement('PRAGMA foreign_keys = ON;');
            });

            $this->newLine();
            $this->info("✅ ĐỒNG BỘ DỮ LIỆU THÀNH CÔNG TỪ PRODUCTION VỀ LOCALHOST!");
            $this->table(
                ['Bảng Dữ Liệu', 'Số Lượng Bản Ghi Production'],
                [
                    ['👥 Users (Thành viên)', count($data['users'] ?? [])],
                    ['🏛️ Funds (Quỹ chung)', count($data['funds'] ?? [])],
                    ['📂 Projects (Dự án)', count($data['projects'] ?? [])],
                    ['🤝 Project_Members (Bảng phân bổ)', count($data['project_members'] ?? [])],
                    ['📜 Transactions (Lịch sử GD)', count($data['transactions'] ?? [])],
                    ['📊 Distributions (Phân chia)', count($data['distributions'] ?? [])],
                ]
            );

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Có lỗi xảy ra trong quá trình đồng bộ: " . $e->getMessage());
            return 1;
        }
    }
}
