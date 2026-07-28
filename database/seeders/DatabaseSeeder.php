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
            'balance' => 0.00,
            'total_profit' => 126160.00,
        ]);

        $defaultPassword = Hash::make('1322');

        // 2. Create All 8 Team Members from DuLieuDaCo
        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@weamis.com',
            'password' => $defaultPassword,
            'role' => 'admin',
            'avatar' => 'HV',
            'share_percentage' => 25.00,
            'current_debt' => 0.00,
        ]);

        $viet = $admin; // Nguyễn Hoàng Việt

        $son = User::create([
            'name' => 'Hồ Trung Sơn',
            'email' => 'son.ht@weamis.com',
            'password' => Hash::make('password'),
            'role' => 'member',
            'avatar' => 'TS',
            'share_percentage' => 20.00,
            'current_debt' => 0.00,
        ]);

        $duc = User::create([
            'name' => 'Nguyễn Quý Đức',
            'email' => 'duc.nq@weamis.com',
            'password' => Hash::make('password'),
            'role' => 'member',
            'avatar' => 'QĐ',
            'share_percentage' => 15.00,
            'current_debt' => 0.00,
        ]);

        $hung = User::create([
            'name' => 'Nguyễn Đăng Phúc Hưng',
            'email' => 'hung.ndp@weamis.com',
            'password' => Hash::make('password'),
            'role' => 'member',
            'avatar' => 'PH',
            'share_percentage' => 15.00,
            'current_debt' => 0.00,
        ]);

        $kien = User::create([
            'name' => 'Nguyễn Trung Kiên',
            'email' => 'kien.nt@weamis.com',
            'password' => Hash::make('password'),
            'role' => 'member',
            'avatar' => 'TK',
            'share_percentage' => 10.00,
            'current_debt' => 0.00,
        ]);

        $hoanganh = User::create([
            'name' => 'Vũ Đức Hoàng Anh',
            'email' => 'anh.vdh@weamis.com',
            'password' => Hash::make('password'),
            'role' => 'member',
            'avatar' => 'HA',
            'share_percentage' => 5.00,
            'current_debt' => 0.00,
        ]);

        $thanhan = User::create([
            'name' => 'Lê Văn Thành An',
            'email' => 'an.lvt@weamis.com',
            'password' => Hash::make('password'),
            'role' => 'member',
            'avatar' => 'TA',
            'share_percentage' => 5.00,
            'current_debt' => 0.00,
        ]);

        $minh = User::create([
            'name' => 'Trịnh Quang Minh',
            'email' => 'minh.tq@weamis.com',
            'password' => Hash::make('password'),
            'role' => 'member',
            'avatar' => 'QM',
            'share_percentage' => 5.00,
            'current_debt' => 0.00,
        ]);

        // 3. Create Real Transactions Extracted From DuLieuDaCo MoMo Screenshots
        $now = Carbon::now();

        $transactionsData = [
            // 6-5 months ago (Older activity)
            ['user' => $viet, 'type' => 'contribution', 'amount' => 1200000, 'desc' => 'Góp vào quỹ chung', 'days' => 180],
            ['user' => $son, 'type' => 'contribution', 'amount' => 2000000, 'desc' => 'đóng bát năm mới', 'days' => 150],
            ['user' => $son, 'type' => 'contribution', 'amount' => 1000000, 'desc' => 'thèm bánh mì que cay', 'days' => 150],
            ['user' => $son, 'type' => 'contribution', 'amount' => 247766, 'desc' => 'Góp vào quỹ chung', 'days' => 150],
            ['user' => $duc, 'type' => 'contribution', 'amount' => 200000, 'desc' => 'Góp vào quỹ chung', 'days' => 150],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 500000, 'desc' => 'CTO vắt cổ chày ra nước ủng hộ kèo 360', 'days' => 150],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 5000000, 'desc' => 'Tiền Everbloom', 'days' => 150],
            ['user' => $kien, 'type' => 'contribution', 'amount' => 1000000, 'desc' => 'Trả nợ vay ngày 08/03', 'days' => 150],
            ['user' => $viet, 'type' => 'loan', 'amount' => 1000000, 'desc' => 'Kin vay', 'days' => 150],
            ['user' => $viet, 'type' => 'expense', 'amount' => 390000, 'desc' => 'Chi tiền mặt + nước: Minh, Hưng, Việt', 'days' => 150],
            ['user' => $viet, 'type' => 'expense', 'amount' => 920000, 'desc' => 'Tiền đớp ăn vặt hp', 'days' => 150],
            ['user' => $viet, 'type' => 'expense', 'amount' => 2250000, 'desc' => 'Rút tiền lương', 'days' => 150],
            ['user' => $viet, 'type' => 'loan', 'amount' => 1000000, 'desc' => 'Chí phèo cào mặt ăn vạ vay quỹ', 'days' => 150],

            // 4 months ago
            ['user' => $son, 'type' => 'contribution', 'amount' => 1000000, 'desc' => 'loc dau thang', 'days' => 120],
            ['user' => $duc, 'type' => 'repayment', 'amount' => 1000000, 'desc' => 'Giả lọ quỹ. Cảm ơn ae', 'days' => 120],
            ['user' => $duc, 'type' => 'contribution', 'amount' => 600000, 'desc' => 'Góp vào quỹ chung', 'days' => 120],

            // 3 months ago
            ['user' => $hung, 'type' => 'expense', 'amount' => 1700000, 'desc' => 'Mmb villa', 'days' => 90],
            ['user' => $hung, 'type' => 'contribution', 'amount' => 60000, 'desc' => 'Góp vào quỹ chung', 'days' => 90],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 500000, 'desc' => 'Chộ nhận sứng', 'days' => 90],
            ['user' => $viet, 'type' => 'repayment', 'amount' => 1000000, 'desc' => 'Trả nợ tiền răng cho chí phèo', 'days' => 90],
            ['user' => $son, 'type' => 'loan', 'amount' => 1000000, 'desc' => 'trang trải cuộc sống cuối tháng', 'days' => 90],
            ['user' => $kien, 'type' => 'contribution', 'amount' => 560000, 'desc' => 'Góp vào quỹ chung', 'days' => 90],
            ['user' => $kien, 'type' => 'contribution', 'amount' => 500000, 'desc' => 'Góp vào quỹ chung', 'days' => 90],
            ['user' => $son, 'type' => 'repayment', 'amount' => 710000, 'desc' => 'bat ca', 'days' => 90],
            ['user' => $hung, 'type' => 'contribution', 'amount' => 650000, 'desc' => 'Góp vào quỹ chung', 'days' => 90],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 700000, 'desc' => 'Góp vào quỹ chung', 'days' => 90],
            ['user' => $kien, 'type' => 'loan', 'amount' => 500000, 'desc' => 'Mượn đi đánh lô', 'days' => 90],
            ['user' => $viet, 'type' => 'loan', 'amount' => 5477300, 'desc' => 'Bú tiền ứng cát bà', 'days' => 90],
            ['user' => $hung, 'type' => 'contribution', 'amount' => 1400000, 'desc' => 'Dư cọc villa', 'days' => 90],
            ['user' => $duc, 'type' => 'expense', 'amount' => 1000000, 'desc' => 'Đi rửa chân', 'days' => 90],

            // 2 months ago
            ['user' => $thanhan, 'type' => 'contribution', 'amount' => 1200000, 'desc' => 'ngon ngay', 'days' => 60],
            ['user' => $viet, 'type' => 'repayment', 'amount' => 1700000, 'desc' => 'Trả nợ + tiền cát bà chí phèo', 'days' => 60],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 700000, 'desc' => 'Chộ nhận sứng', 'days' => 60],
            ['user' => $hung, 'type' => 'expense', 'amount' => 170000, 'desc' => 'Gac', 'days' => 60],
            ['user' => $hung, 'type' => 'contribution', 'amount' => 3250000, 'desc' => 'Góp vào quỹ chung', 'days' => 60],
            ['user' => $hung, 'type' => 'contribution', 'amount' => 3250000, 'desc' => 'Wifi lalot', 'days' => 60],

            // 1 month ago
            ['user' => $viet, 'type' => 'loan', 'amount' => 3000000, 'desc' => 'vay 3 củ đóng học phí', 'days' => 30],
            ['user' => $duc, 'type' => 'loan', 'amount' => 1000000, 'desc' => 'Giai cuu Chi Pheo mua World cup 😭', 'days' => 30],
            ['user' => $hoanganh, 'type' => 'loan', 'amount' => 1000000, 'desc' => 'Đói kém, xin lương :(((', 'days' => 30],
            ['user' => $kien, 'type' => 'loan', 'amount' => 3000000, 'desc' => 'Mua ram, đói kém', 'days' => 30],
            ['user' => $viet, 'type' => 'expense', 'amount' => 3233520, 'desc' => 'Chuyển tiền đến BBBTHANGLONG CN Lau Phan Dao Duy Anh (PVComBank Pay) thanh toan don hang 634248107', 'days' => 30],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 700000, 'desc' => 'Góp 10% cns', 'days' => 30],
            ['user' => $viet, 'type' => 'repayment', 'amount' => 3000000, 'desc' => 'CTO trả nợ tiền học', 'days' => 30],

            // Recent (18 - 1 days ago)
            ['user' => $hung, 'type' => 'contribution', 'amount' => 700000, 'desc' => 'Tán lộc', 'days' => 18],
            ['user' => $minh, 'type' => 'expense', 'amount' => 150000, 'desc' => 'mua kìm đấu wifi', 'days' => 10],
            ['user' => $kien, 'type' => 'expense', 'amount' => 535000, 'desc' => 'Quỹ networking với anh 3T - Tri ân vi da den', 'days' => 1],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 900000, 'desc' => 'CTO góp cns tháng 7', 'days' => 1],
        ];

        $currentBalance = 0;

        foreach ($transactionsData as $item) {
            $u = $item['user'];
            $type = $item['type'];
            $amount = $item['amount'];

            // Calculate balance impact
            if ($type === 'contribution' || $type === 'repayment') {
                $currentBalance += $amount;
                if ($type === 'repayment') {
                    $u->current_debt = max(0, $u->current_debt - $amount);
                }
            } else if ($type === 'expense' || $type === 'loan') {
                $currentBalance -= $amount;
                if ($type === 'loan') {
                    $u->current_debt += $amount;
                }
            }
            $u->save();

            Transaction::create([
                'fund_id' => $fund->id,
                'user_id' => $u->id,
                'type' => $type,
                'amount' => $amount,
                'description' => $item['desc'],
                'status' => 'approved',
                'approved_by' => $admin->id,
                'created_at' => $now->copy()->subDays($item['days']),
            ]);
        }

        // Set final calculated balance on Fund
        $fund->balance = max(0, $currentBalance);
        $fund->save();
    }
}
