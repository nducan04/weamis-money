<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Fund;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
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

        // 2. Create Admin Account and All 8 Team Members
        $admin = User::create([
            'name' => 'Quản Trị Viên (Admin)',
            'username' => 'admin',
            'email' => 'admin@weamis.com',
            'password' => \Illuminate\Support\Facades\Hash::make('1322'),
            'role' => 'admin',
            'avatar' => 'AD',
            'share_percentage' => 0.00,
            'current_debt' => 0.00,
        ]);

        $viet = User::create([
            'name' => 'Nguyễn Hoàng Việt',
            'username' => 'nhv',
            'email' => 'viet.nh@weamis.com',
            'password' => \Illuminate\Support\Facades\Hash::make('1234'),
            'role' => 'member',
            'avatar' => 'HV',
            'share_percentage' => 25.00,
            'current_debt' => 0.00,
        ]);

        $son = User::create([
            'name' => 'Hồ Trung Sơn',
            'email' => 'son.ht@weamis.com',
            'role' => 'member',
            'avatar' => 'TS',
            'share_percentage' => 20.00,
            'current_debt' => 0.00,
        ]);

        $duc = User::create([
            'name' => 'Nguyễn Quý Đức',
            'email' => 'duc.nq@weamis.com',
            'role' => 'member',
            'avatar' => 'QĐ',
            'share_percentage' => 15.00,
            'current_debt' => 0.00,
        ]);

        $hung = User::create([
            'name' => 'Nguyễn Đăng Phúc Hưng',
            'email' => 'hung.ndp@weamis.com',
            'role' => 'member',
            'avatar' => 'PH',
            'share_percentage' => 15.00,
            'current_debt' => 0.00,
        ]);

        $kien = User::create([
            'name' => 'Nguyễn Trung Kiên',
            'email' => 'kien.nt@weamis.com',
            'role' => 'member',
            'avatar' => 'TK',
            'share_percentage' => 10.00,
            'current_debt' => 0.00,
        ]);

        $hoanganh = User::create([
            'name' => 'Vũ Đức Hoàng Anh',
            'email' => 'anh.vdh@weamis.com',
            'role' => 'member',
            'avatar' => 'HA',
            'share_percentage' => 5.00,
            'current_debt' => 0.00,
        ]);

        $thanhan = User::create([
            'name' => 'Lê Văn Thành An',
            'email' => 'an.lvt@weamis.com',
            'role' => 'member',
            'avatar' => 'TA',
            'share_percentage' => 5.00,
            'current_debt' => 0.00,
        ]);

        $minh = User::create([
            'name' => 'Trịnh Quang Minh',
            'email' => 'minh.tq@weamis.com',
            'role' => 'member',
            'avatar' => 'QM',
            'share_percentage' => 5.00,
            'current_debt' => 0.00,
        ]);

        // 3. Create Real Chronological Transactions from anh1.jpg to anh13.jpg
        $now = Carbon::now();

        $transactionsData = [
            // anh1.jpg & anh4.jpg (6 - 5 tháng trước)
            ['user' => $viet, 'type' => 'contribution', 'amount' => 1200000, 'desc' => 'Góp vào quỹ chung', 'days' => 180],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 5000000, 'desc' => 'Tiền Everbloom', 'days' => 155],
            ['user' => $son, 'type' => 'contribution', 'amount' => 2000000, 'desc' => 'đóng bát năm mới', 'days' => 150],
            ['user' => $son, 'type' => 'contribution', 'amount' => 1000000, 'desc' => 'thèm bánh mì que cay', 'days' => 148],
            ['user' => $duc, 'type' => 'contribution', 'amount' => 200000, 'desc' => 'Góp vào quỹ chung', 'days' => 146],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 500000, 'desc' => 'CTO vắt cổ chày ra nước ủng hộ kèo 360', 'days' => 144],

            // anh10.jpg & anh5.jpg (5 tháng trước)
            ['user' => $viet, 'type' => 'loan', 'amount' => 1000000, 'desc' => 'Chí phèo cào mặt ăn vạ vay quỹ', 'days' => 142],
            ['user' => $viet, 'type' => 'expense', 'amount' => 2250000, 'desc' => 'Rút tiền lương', 'days' => 140],
            ['user' => $son, 'type' => 'contribution', 'amount' => 247766, 'desc' => 'Góp vào quỹ chung', 'days' => 138],
            ['user' => $viet, 'type' => 'expense', 'amount' => 920000, 'desc' => 'Tiền đớp ăn vặt hp', 'days' => 136],
            ['user' => $viet, 'type' => 'expense', 'amount' => 390000, 'desc' => 'Chi tiền mặt + nước: Minh, Hưng, Việt', 'days' => 134],
            ['user' => $viet, 'type' => 'loan', 'amount' => 1000000, 'desc' => 'Kin vay', 'days' => 132],
            ['user' => $kien, 'type' => 'repayment', 'amount' => 1000000, 'desc' => 'Trả nợ vay ngày 08/03', 'days' => 130],

            // anh10.jpg & anh2.jpg (4 - 3 tháng trước)
            ['user' => $son, 'type' => 'contribution', 'amount' => 1000000, 'desc' => 'loc dau thang', 'days' => 125],
            ['user' => $duc, 'type' => 'contribution', 'amount' => 600000, 'desc' => 'Góp vào quỹ chung', 'days' => 120],
            ['user' => $duc, 'type' => 'repayment', 'amount' => 1000000, 'desc' => 'Giả lọ quỹ. Cảm ơn ae', 'days' => 118],
            ['user' => $viet, 'type' => 'expense', 'amount' => 1000000, 'desc' => 'tiền chí phèo bao thịt chó', 'days' => 116],

            // anh2.jpg, anh8.jpg, anh7.jpg, anh3.jpg (3 tháng trước)
            ['user' => $hung, 'type' => 'expense', 'amount' => 1700000, 'desc' => 'Mmb villa', 'days' => 100],
            ['user' => $hung, 'type' => 'contribution', 'amount' => 1400000, 'desc' => 'Dư cọc villa', 'days' => 98],
            ['user' => $viet, 'type' => 'loan', 'amount' => 5477300, 'desc' => 'Bú tiền ứng cát bà', 'days' => 96],
            ['user' => $kien, 'type' => 'loan', 'amount' => 500000, 'desc' => 'Mượn đi đánh lô', 'days' => 94],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 700000, 'desc' => 'Góp vào quỹ chung', 'days' => 92],
            ['user' => $hung, 'type' => 'contribution', 'amount' => 650000, 'desc' => 'Góp vào quỹ chung', 'days' => 90],
            ['user' => $son, 'type' => 'repayment', 'amount' => 710000, 'desc' => 'bat ca', 'days' => 88],
            ['user' => $kien, 'type' => 'contribution', 'amount' => 500000, 'desc' => 'Góp vào quỹ chung', 'days' => 86],
            ['user' => $kien, 'type' => 'contribution', 'amount' => 560000, 'desc' => 'Góp vào quỹ chung', 'days' => 84],
            ['user' => $son, 'type' => 'loan', 'amount' => 1000000, 'desc' => 'trang trải cuộc sống cuối tháng', 'days' => 82],
            ['user' => $viet, 'type' => 'repayment', 'amount' => 1000000, 'desc' => 'Trả nợ tiền răng cho chí phèo', 'days' => 80],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 500000, 'desc' => 'Chộ nhận sứng', 'days' => 78],
            ['user' => $hung, 'type' => 'contribution', 'amount' => 60000, 'desc' => 'Góp vào quỹ chung', 'days' => 76],
            ['user' => $duc, 'type' => 'expense', 'amount' => 1000000, 'desc' => 'Đi rửa chân', 'days' => 74],

            // anh12.jpg, anh11.jpg, anh6.jpg (2 tháng trước)
            ['user' => $hung, 'type' => 'contribution', 'amount' => 3250000, 'desc' => 'Wifi lalot', 'days' => 65],
            ['user' => $thanhan, 'type' => 'contribution', 'amount' => 1200000, 'desc' => 'ngon ngay', 'days' => 60],
            ['user' => $viet, 'type' => 'repayment', 'amount' => 1700000, 'desc' => 'Trả nợ + tiền cát bà chí phèo', 'days' => 58],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 700000, 'desc' => 'Chộ nhận sứng', 'days' => 56],
            ['user' => $hung, 'type' => 'expense', 'amount' => 170000, 'desc' => 'Gac', 'days' => 54],
            ['user' => $hung, 'type' => 'contribution', 'amount' => 3250000, 'desc' => 'Góp vào quỹ chung', 'days' => 50],

            // anh6.jpg & anh9.jpg (1 tháng trước)
            ['user' => $hoanganh, 'type' => 'loan', 'amount' => 1000000, 'desc' => 'Đói kém, xin lương :(((', 'days' => 35],
            ['user' => $duc, 'type' => 'loan', 'amount' => 1000000, 'desc' => 'Giai cuu Chi Pheo mua World cup 😭', 'days' => 34],
            ['user' => $viet, 'type' => 'loan', 'amount' => 3000000, 'desc' => 'vay 3 củ đóng học phí', 'days' => 32],
            ['user' => $viet, 'type' => 'repayment', 'amount' => 3000000, 'desc' => 'CTO trả nợ tiền học', 'days' => 30],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 700000, 'desc' => 'Góp 10% cns', 'days' => 28],
            ['user' => $viet, 'type' => 'expense', 'amount' => 3233520, 'desc' => 'Chuyển tiền đến BBBTHANGLONG CN Lau Phan Dao Duy Anh (PVComBank Pay) thanh toan don hang 634248107', 'days' => 25],
            ['user' => $kien, 'type' => 'loan', 'amount' => 3000000, 'desc' => 'Mua ram, đói kém', 'days' => 22],

            // anh13.jpg (Gần đây nhất: 19 ngày -> 1 ngày trước)
            ['user' => $kien, 'type' => 'repayment', 'amount' => 3000000, 'desc' => 'Trả nợ mua ram', 'days' => 19],
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

        // Set exact real MoMo balance on Fund
        $fund->balance = 7028106.00;
        $fund->save();

        // Run UserPasswordSeeder to assign usernames and Bcrypt hashed passwords
        $this->call(UserPasswordSeeder::class);

        // Run ProjectSeeder to seed exact 4 Product projects and their member shares
        $this->call(ProjectSeeder::class);
    }
}
