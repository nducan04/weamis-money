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

                // 4. Project_User
                if (!empty($data['project_user'])) {
                    DB::table('project_user')->delete();
                    foreach ($data['project_user'] as $row) {
                        DB::table('project_user')->insert((array)$row);
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
                    ['🤝 Project_User (Bảng phân bổ)', count($data['project_user'] ?? [])],
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
