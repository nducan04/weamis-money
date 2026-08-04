<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Fund;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    public function up(): void
    {
        $admin = User::where('role', 'admin')->first();
        $viet = User::where('username', 'nhv')->first();
        $hoangAnh = User::where('username', 'vdha')->first();
        $minh = User::where('username', 'tqm')->first();

        // 1. Fix 2,250,000 Rút tiền lương transaction -> assign to Hoàng Anh
        $txSalary = Transaction::where('description', 'like', '%Rút tiền lương%')
            ->where('amount', 2250000)
            ->first();

        if ($txSalary && $hoangAnh) {
            $txSalary->user_id = $hoangAnh->id;
            $txSalary->type = 'withdrawal';
            $txSalary->save();
        }

        // 2. Add Việt +200,000 (Trả nợ giúp Phúc Đăng) if missing
        if ($viet && $admin) {
            $txViet200 = Transaction::where('user_id', $viet->id)
                ->where('amount', 200000)
                ->where('description', 'like', '%Phúc Đ%200k%cát bà%')
                ->first();

            if (!$txViet200) {
                Transaction::create([
                    'fund_id' => Fund::first()?->id ?? 1,
                    'user_id' => $viet->id,
                    'type' => 'contribution',
                    'amount' => 200000,
                    'description' => 'Trả nợ giúp em Phúc Đăng 200k tiền cát bà, còn lại nợ 500k',
                    'status' => 'approved',
                    'approved_by' => $admin->id,
                    'created_at' => '2026-08-01 10:00:00',
                ]);
            }
        }

        // 3. Add Quang Minh -100,000 (tiền ăn chè) if missing
        if ($minh && $admin) {
            $txMinh100 = Transaction::where('user_id', $minh->id)
                ->where('amount', 100000)
                ->where('description', 'like', '%ăn chè%')
                ->first();

            if (!$txMinh100) {
                Transaction::create([
                    'fund_id' => Fund::first()?->id ?? 1,
                    'user_id' => $minh->id,
                    'type' => 'expense',
                    'amount' => 100000,
                    'description' => 'tiền ăn chè',
                    'status' => 'approved',
                    'approved_by' => $admin->id,
                    'created_at' => '2026-08-04 12:00:00',
                ]);
            }
        }

        // 4. Update Fund balance & total_profit (Túi Thần Tài)
        $fund = Fund::first();
        if ($fund) {
            $fund->balance = 7133503.00;
            $fund->total_profit = 1200000.00;
            $fund->save();
        }

        // 5. Also run seeder to be 100% sure
        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\Seeders\DatabaseSeeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            // Ignore if seeding throws due to existing records
        }
    }

    public function down(): void
    {
        // No-op
    }
};
