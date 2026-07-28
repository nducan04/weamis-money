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

        // 2. Create Users
        $viet = User::create([
            'name' => 'Nguyễn Hoàng Việt',
            'email' => 'viet.nh@weamis.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'avatar' => 'HV',
            'share_percentage' => 40.00,
            'current_debt' => 0.00,
        ]);

        $kien = User::create([
            'name' => 'Nguyễn Trung Kiên',
            'email' => 'kien.nt@weamis.com',
            'password' => Hash::make('password'),
            'role' => 'member',
            'avatar' => 'TK',
            'share_percentage' => 30.00,
            'current_debt' => 0.00,
        ]);

        $duc = User::create([
            'name' => 'Nguyễn Quý Đức',
            'email' => 'duc.nq@weamis.com',
            'password' => Hash::make('password'),
            'role' => 'member',
            'avatar' => 'QĐ',
            'share_percentage' => 30.00,
            'current_debt' => 1000000.00, // Đã vay 1tr chưa trả
        ]);

        // 3. Create Initial MoMo Screenshot Transactions
        $now = Carbon::now();

        // 1. Việt góp cns tháng 7 (+900k) - 20 giờ trước
        Transaction::create([
            'fund_id' => $fund->id,
            'user_id' => $viet->id,
            'type' => 'contribution',
            'amount' => 900000,
            'description' => 'CTO góp cns tháng 7',
            'status' => 'approved',
            'approved_by' => $viet->id,
            'created_at' => $now->copy()->subHours(20),
        ]);

        // 2. Kiên rút quỹ networking (-535k) - 1 ngày trước
        Transaction::create([
            'fund_id' => $fund->id,
            'user_id' => $kien->id,
            'type' => 'expense',
            'amount' => 535000,
            'description' => 'Quỹ networking với anh 3T - Tri ân vi da den',
            'status' => 'approved',
            'approved_by' => $viet->id,
            'created_at' => $now->copy()->subDays(1),
        ]);

        // 3. Việt góp 10% cns (+700k) - 1 tháng trước
        Transaction::create([
            'fund_id' => $fund->id,
            'user_id' => $viet->id,
            'type' => 'contribution',
            'amount' => 700000,
            'description' => 'Góp 10% cns',
            'status' => 'approved',
            'approved_by' => $viet->id,
            'created_at' => $now->copy()->subMonth(),
        ]);

        // 4. Việt trả nợ tiền học (+3m) - 1 tháng trước
        Transaction::create([
            'fund_id' => $fund->id,
            'user_id' => $viet->id,
            'type' => 'repayment',
            'amount' => 3000000,
            'description' => 'CTO trả nợ tiền học',
            'status' => 'approved',
            'approved_by' => $viet->id,
            'created_at' => $now->copy()->subMonth()->subMinutes(10),
        ]);

        // 5. Việt vay 3 củ đóng học phí (-3m) - 1 tháng trước
        Transaction::create([
            'fund_id' => $fund->id,
            'user_id' => $viet->id,
            'type' => 'loan',
            'amount' => 3000000,
            'description' => 'vay 3 củ đóng học phí',
            'status' => 'approved',
            'approved_by' => $viet->id,
            'created_at' => $now->copy()->subMonth()->subMinutes(30),
        ]);

        // 6. Đức vay giải cứu Chí Phèo (-1m) - 1 tháng trước
        Transaction::create([
            'fund_id' => $fund->id,
            'user_id' => $duc->id,
            'type' => 'loan',
            'amount' => 1000000,
            'description' => 'Giai cuu Chi Pheo mua World cup',
            'status' => 'approved',
            'approved_by' => $viet->id,
            'created_at' => $now->copy()->subMonth()->subHours(2),
        ]);
    }
}
