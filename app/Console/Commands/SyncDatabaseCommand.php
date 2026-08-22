<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class SyncDatabaseCommand extends Command
{
    protected $signature = 'sync:db {action=pull : "pull" to download from prod, "push" to upload to prod} {--url= : Production URL (default: env SYNC_PROD_URL or https://money.vietnh.io.vn)} {--key= : Sync Key (default: env DB_SYNC_KEY)}';

    protected $description = 'Đồng bộ 2 chiều Database giữa Local và Production';

    public function handle(): int
    {
        $action = strtolower($this->argument('action'));
        $prodUrl = rtrim($this->option('url') ?: env('SYNC_PROD_URL', 'https://money.vietnh.io.vn'), '/');
        $syncKey = $this->option('key') ?: env('DB_SYNC_KEY', 'weamis_money_secret_sync_key_2026');
        $localDbPath = config('database.connections.sqlite.database');

        $this->info("--------------------------------------------------");
        $this->info("⚡ WEAMIS MONEY - DATABASE SYNC TOOL");
        $this->info("⚡ Hành động: " . strtoupper($action));
        $this->info("⚡ Target URL: {$prodUrl}");
        $this->info("--------------------------------------------------");

        if ($action === 'pull') {
            $this->info("📥 Đang tải dữ liệu từ Production ({$prodUrl})...");
            
            try {
                $response = Http::withHeaders([
                    'X-Sync-Key' => $syncKey,
                ])->timeout(30)->get("{$prodUrl}/api/db-dump");

                if (!$response->successful()) {
                    $this->error("❌ Lỗi tải dữ liệu: HTTP " . $response->status() . " - " . $response->body());
                    return Command::FAILURE;
                }

                $json = $response->json();
                if (empty($json['data'])) {
                    $this->error("❌ Payload dữ liệu không hợp lệ từ Production.");
                    return Command::FAILURE;
                }

                // Import to local
                $importController = new \App\Http\Controllers\DatabaseSyncController();
                $req = new \Illuminate\Http\Request();
                $req->headers->set('X-Sync-Key', $syncKey);
                $req->merge(['data' => $json['data']]);
                
                $result = $importController->importJson($req);
                $resData = json_decode($result->getContent(), true);

                if ($result->getStatusCode() !== 200) {
                    $this->error("❌ Lỗi import vào Local DB: " . ($resData['error'] ?? 'Unknown error'));
                    return Command::FAILURE;
                }

                $this->info("✅ ĐỒNG BỘ THÀNH CÔNG!");
                $this->line("   - Đã đồng bộ toàn bộ bảng dữ liệu (Transactions, Projects, Accounts, Users, Funds, v.v.).");
                $this->line("   - Cache đã được xóa và tối ưu lại.");
                return Command::SUCCESS;
            } catch (\Exception $e) {
                $this->error("❌ Lỗi kết nối: " . $e->getMessage());
                return Command::FAILURE;
            }
        } elseif ($action === 'push') {
            if (!$this->confirm("⚠️ BẠN CÓ CHẮC CHẮN muốn đẩy dữ liệu từ Local lên ghi đè vào Production?")) {
                $this->warn("Đã hủy thao tác.");
                return Command::SUCCESS;
            }

            $this->info("📤 Đang xuất dữ liệu từ Local...");
            $exportController = new \App\Http\Controllers\DatabaseSyncController();
            $req = new \Illuminate\Http\Request();
            $req->headers->set('X-Sync-Key', $syncKey);
            $localDump = json_decode($exportController->export($req)->getContent(), true);

            if (empty($localDump['data'])) {
                $this->error("❌ Không thể xuất dữ liệu Local.");
                return Command::FAILURE;
            }

            $this->info("🚀 Đang đẩy dữ liệu lên Production ({$prodUrl})...");
            try {
                $response = Http::withHeaders([
                    'X-Sync-Key' => $syncKey,
                ])->timeout(30)->post("{$prodUrl}/api/db-import", [
                    'data' => $localDump['data'],
                ]);

                if (!$response->successful()) {
                    $this->error("❌ Lỗi nạp dữ liệu lên Production: HTTP " . $response->status() . " - " . $response->body());
                    return Command::FAILURE;
                }

                $this->info("✅ ĐẨY DỮ LIỆU LÊN PRODUCTION THÀNH CÔNG!");
                return Command::SUCCESS;
            } catch (\Exception $e) {
                $this->error("❌ Lỗi kết nối: " . $e->getMessage());
                return Command::FAILURE;
            }
        } else {
            $this->error("Hành động không hợp lệ. Vui lòng chọn 'pull' hoặc 'push'.");
            return Command::FAILURE;
        }
    }
}
