<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Fund;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Fund
        $fund = Fund::create([
            'name' => 'Trả nợ thuê Ltd',
            'balance' => 7028106.00,
            'total_profit' => 126160.00,
        ]);

        // 2. Create Single Admin User (admin / 1322)
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin',
            'password' => Hash::make('1322'),
            'role' => 'admin',
            'avatar' => 'AD',
            'share_percentage' => 100.00,
            'current_debt' => 0.00,
        ]);

        // 3. Create Sample Transactions for Admin
        $now = Carbon::now();

        // 1. Góp cns tháng 7 (+900k)
        Transaction::create([
            'fund_id' => $fund->id,
            'user_id' => $admin->id,
            'type' => 'contribution',
            'amount' => 900000,
            'description' => 'CTO góp cns tháng 7',
            'status' => 'approved',
            'approved_by' => $admin->id,
            'created_at' => $now->copy()->subHours(20),
        ]);

        // 2. Chi tiêu networking (-535k)
        Transaction::create([
            'fund_id' => $fund->id,
            'user_id' => $admin->id,
            'type' => 'expense',
            'amount' => 535000,
            'description' => 'Quỹ networking với anh 3T',
            'status' => 'approved',
            'approved_by' => $admin->id,
            'created_at' => $now->copy()->subDays(1),
        ]);

        // 3. Góp 10% cns (+700k)
        Transaction::create([
            'fund_id' => $fund->id,
            'user_id' => $admin->id,
            'type' => 'contribution',
            'amount' => 700000,
            'description' => 'Góp 10% cns',
            'status' => 'approved',
            'approved_by' => $admin->id,
            'created_at' => $now->copy()->subMonth(),
        ]);

        // 4. Trả nợ tiền học (+3m)
        Transaction::create([
            'fund_id' => $fund->id,
            'user_id' => $admin->id,
            'type' => 'repayment',
            'amount' => 3000000,
            'description' => 'CTO trả nợ tiền học',
            'status' => 'approved',
            'approved_by' => $admin->id,
            'created_at' => $now->copy()->subMonth()->subMinutes(10),
        ]);

        // 5. Vay 3 củ đóng học phí (-3m)
        Transaction::create([
            'fund_id' => $fund->id,
            'user_id' => $admin->id,
            'type' => 'loan',
            'amount' => 3000000,
            'description' => 'Vay 3 củ đóng học phí',
            'status' => 'approved',
            'approved_by' => $admin->id,
            'created_at' => $now->copy()->subMonth()->subMinutes(30),
        ]);
    }
}
