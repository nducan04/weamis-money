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
        // Clear existing data cleanly for fresh re-seeding
        Transaction::query()->delete();
        User::query()->delete();
        Fund::query()->delete();

        // 1. Create Fund
        $fund = Fund::create([
            'name' => 'Trả nợ thuê Ltd',
            'balance' => 7133503.00,
            'total_profit' => 1200000.00,
        ]);

        // 2. Create Admin Account and All 8 Real Team Members
        $admin = User::create([
            'name' => 'Quản Trị Viên (Admin)',
            'username' => 'admin',
            'email' => 'admin@weamis.com',
            'password' => Hash::make('1322'),
            'role' => 'admin',
            'avatar' => 'AD',
            'share_percentage' => 0.00,
            'current_debt' => 0.00,
        ]);

        $viet = User::create([
            'name' => 'Nguyễn Hoàng Việt',
            'username' => 'nhv',
            'email' => 'viet.nh@weamis.com',
            'password' => Hash::make('1234'),
            'role' => 'member',
            'avatar' => 'HV',
            'share_percentage' => 25.00,
            'current_debt' => 0.00,
        ]);

        $son = User::create([
            'name' => 'Hồ Trùng Sơn',
            'username' => 'sonht',
            'email' => 'son.ht@weamis.com',
            'password' => Hash::make('1234'),
            'role' => 'member',
            'avatar' => 'TS',
            'share_percentage' => 20.00,
            'current_debt' => 0.00,
        ]);

        $duc = User::create([
            'name' => 'Nguyễn Quý Đức',
            'username' => 'ducnq',
            'email' => 'duc.nq@weamis.com',
            'password' => Hash::make('1234'),
            'role' => 'member',
            'avatar' => 'QĐ',
            'share_percentage' => 15.00,
            'current_debt' => 0.00,
        ]);

        $hung = User::create([
            'name' => 'Nguyễn Đăng Phúc Hưng',
            'username' => 'hungndp',
            'email' => 'hung.ndp@weamis.com',
            'password' => Hash::make('1234'),
            'role' => 'member',
            'avatar' => 'PH',
            'share_percentage' => 15.00,
            'current_debt' => 0.00,
        ]);

        $kien = User::create([
            'name' => 'Nguyễn Trung Kiên',
            'username' => 'kiennt',
            'email' => 'kien.nt@weamis.com',
            'password' => Hash::make('1234'),
            'role' => 'member',
            'avatar' => 'TK',
            'share_percentage' => 10.00,
            'current_debt' => 0.00,
        ]);

        $hoanganh = User::create([
            'name' => 'Vũ Đức Hoàng Anh',
            'username' => 'anhvdh',
            'email' => 'anh.vdh@weamis.com',
            'password' => Hash::make('1234'),
            'role' => 'member',
            'avatar' => 'HA',
            'share_percentage' => 5.00,
            'current_debt' => 0.00,
        ]);

        $thanhan = User::create([
            'name' => 'Lê Văn Thành An',
            'username' => 'anlvt',
            'email' => 'an.lvt@weamis.com',
            'password' => Hash::make('1234'),
            'role' => 'member',
            'avatar' => 'TA',
            'share_percentage' => 5.00,
            'current_debt' => 0.00,
        ]);

        $minh = User::create([
            'name' => 'Trịnh Quang Minh',
            'username' => 'minhtq',
            'email' => 'minh.tq@weamis.com',
            'password' => Hash::make('1234'),
            'role' => 'member',
            'avatar' => 'QM',
            'share_percentage' => 5.00,
            'current_debt' => 0.00,
        ]);

        $ducAn = User::create([
            'name' => 'Nguyễn Đức An',
            'username' => 'nda',
            'email' => 'an.nd@weamis.com',
            'password' => Hash::make('1234'),
            'role' => 'member',
            'avatar' => 'DA',
            'share_percentage' => 0.00,
            'current_debt' => 0.00,
        ]);

        // 3. Exact 41 Transactions from Google Sheet
        $transactionsData = [
            ['user' => $viet, 'type' => 'contribution', 'amount' => 1200000, 'desc' => 'Góp vào quỹ chung', 'datetime' => '2026-02-12 15:10:00'],
            ['user' => $admin, 'type' => 'contribution', 'amount' => 1200000, 'desc' => 'Nạp tiền vào Túi Thần Tài (Tích lũy sinh lời)', 'datetime' => '2026-02-12 15:10:00'],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 5000000, 'desc' => 'Tiền Everbloom', 'datetime' => '2026-02-15 17:37:00'],
            ['user' => $son, 'type' => 'contribution', 'amount' => 2000000, 'desc' => 'đóng bát năm mới', 'datetime' => '2026-02-16 23:16:00'],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 500000, 'desc' => 'CTO vắt cổ chày ra nước ủng hộ kèo 3', 'datetime' => '2026-02-17 12:11:00'],
            ['user' => $duc, 'type' => 'contribution', 'amount' => 200000, 'desc' => 'Góp vào quỹ chung', 'datetime' => '2026-02-17 12:11:00'],
            ['user' => $son, 'type' => 'contribution', 'amount' => 1000000, 'desc' => 'thèm bánh mì que cay', 'datetime' => '2026-02-23 11:27:00'],
            ['user' => $viet, 'type' => 'expense', 'amount' => 920000, 'desc' => 'Tiền đớp ăn vặt hp', 'datetime' => '2026-03-01 18:28:00'],
            ['user' => $viet, 'type' => 'expense', 'amount' => 390000, 'desc' => 'Chi tiền mặt + nước: Minh Hưng Việt', 'datetime' => '2026-03-01 22:33:00'],
            ['user' => $viet, 'type' => 'loan', 'amount' => 1000000, 'desc' => 'Kin vay', 'datetime' => '2026-03-08 12:33:00'],
            ['user' => $kien, 'type' => 'repayment', 'amount' => 1000000, 'desc' => 'Trả nợ vay ngày 08/03', 'datetime' => '2026-03-09 11:36:00'],
            ['user' => $viet, 'type' => 'loan', 'amount' => 1000000, 'desc' => 'Chí phèo cào mặt ăn va quy', 'datetime' => '2026-03-10 21:25:00'],
            ['user' => $hoanganh, 'type' => 'withdrawal', 'amount' => 2250000, 'desc' => 'Rút tiền lương', 'datetime' => '2026-03-11 20:03:00'],  // Sheet: Hoàng Anh
            ['user' => $son, 'type' => 'contribution', 'amount' => 247766, 'desc' => 'Góp vào quỹ chung', 'datetime' => '2026-03-13 03:01:00'],
            ['user' => $son, 'type' => 'contribution', 'amount' => 1000000, 'desc' => 'loc dau thang', 'datetime' => '2026-04-02 09:58:00'],
            ['user' => $duc, 'type' => 'contribution', 'amount' => 600000, 'desc' => 'Góp vào quỹ chung', 'datetime' => '2026-04-11 10:39:00'],
            ['user' => $duc, 'type' => 'repayment', 'amount' => 1000000, 'desc' => 'Giả lọ quỹ. Cảm ơn ae', 'datetime' => '2026-04-11 10:40:00'],
            ['user' => $viet, 'type' => 'loan', 'amount' => 1000000, 'desc' => 'tiền chí phèo bao thịt', 'datetime' => '2026-04-11 19:00:00'],
            ['user' => $hung, 'type' => 'expense', 'amount' => 1700000, 'desc' => 'Mmb villa', 'datetime' => '2026-04-17 21:17:00'],
            ['user' => $son, 'type' => 'loan', 'amount' => 1000000, 'desc' => 'trang trải cuộc sống cuối tháng', 'datetime' => '2026-04-22 15:36:00'],
            ['user' => $viet, 'type' => 'repayment', 'amount' => 1000000, 'desc' => 'Trả nợ tiền răng cho chí phèo', 'datetime' => '2026-04-23 21:26:00'],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 500000, 'desc' => 'Chộ nhận sứng', 'datetime' => '2026-04-23 21:31:00'],
            ['user' => $hung, 'type' => 'contribution', 'amount' => 60000, 'desc' => 'Góp vào quỹ chung', 'datetime' => '2026-04-28 23:26:00'],
            ['user' => $hung, 'type' => 'contribution', 'amount' => 1400000, 'desc' => 'Dư cọc villa', 'datetime' => '2026-04-28 23:42:00'],
            ['user' => $viet, 'type' => 'loan', 'amount' => 5477300, 'desc' => 'Bú tiền ứng cát bà', 'datetime' => '2026-04-29 00:18:00'],
            ['user' => $kien, 'type' => 'loan', 'amount' => 500000, 'desc' => 'Mượn đi đánh lô', 'datetime' => '2026-05-07 09:43:00'],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 700000, 'desc' => 'Góp vào quỹ chung', 'datetime' => '2026-05-07 10:40:00'],
            ['user' => $hung, 'type' => 'contribution', 'amount' => 650000, 'desc' => 'Góp vào quỹ chung', 'datetime' => '2026-05-07 10:58:00'],
            ['user' => $son, 'type' => 'contribution', 'amount' => 710000, 'desc' => 'bat ca', 'datetime' => '2026-05-07 13:04:00'],
            ['user' => $kien, 'type' => 'contribution', 'amount' => 500000, 'desc' => 'Góp vào quỹ chung', 'datetime' => '2026-05-07 15:55:00'],
            ['user' => $kien, 'type' => 'contribution', 'amount' => 560000, 'desc' => 'Góp vào quỹ chung', 'datetime' => '2026-05-07 20:31:00'],
            ['user' => $duc, 'type' => 'loan', 'amount' => 1000000, 'desc' => 'Đi rửa chân', 'datetime' => '2026-05-08 16:13:00'],
            ['user' => $hung, 'type' => 'contribution', 'amount' => 3250000, 'desc' => 'Wifi lalot', 'datetime' => '2026-05-18 14:29:00'],
            ['user' => $hung, 'type' => 'expense', 'amount' => 170000, 'desc' => 'Gac', 'datetime' => '2026-05-19 21:27:00'],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 700000, 'desc' => 'Chộ nhận sứng', 'datetime' => '2026-06-04 22:57:00'],
            ['user' => $viet, 'type' => 'repayment', 'amount' => 1700000, 'desc' => 'Trả nợ + tiền cát bà chí phèo', 'datetime' => '2026-06-04 22:58:00'],
            ['user' => $thanhan, 'type' => 'contribution', 'amount' => 1200000, 'desc' => 'ngon ngay', 'datetime' => '2026-06-05 10:43:00'],
            ['user' => $hung, 'type' => 'contribution', 'amount' => 3250000, 'desc' => 'Góp vào quỹ chung', 'datetime' => '2026-06-05 13:24:00'],
            ['user' => $hoanganh, 'type' => 'withdrawal', 'amount' => 1000000, 'desc' => 'Đói kém xin lương :((((', 'datetime' => '2026-06-16 16:52:00'],
            ['user' => $duc, 'type' => 'loan', 'amount' => 1000000, 'desc' => 'Giai cuu Chi Pheo mua World cup', 'datetime' => '2026-06-16 19:09:00'],
            ['user' => $viet, 'type' => 'loan', 'amount' => 3000000, 'desc' => 'vay 3 củ đóng học phí', 'datetime' => '2026-06-16 19:12:00'],

            // === Post-sheet transactions (after 16/06/2026) ===
            ['user' => $viet, 'type' => 'repayment', 'amount' => 3000000, 'desc' => 'CTO trả nợ tiền học', 'datetime' => '2026-06-30 12:00:00'],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 700000, 'desc' => 'Góp 10% cns', 'datetime' => '2026-07-02 12:00:00'],
            ['user' => $viet, 'type' => 'expense', 'amount' => 3233520, 'desc' => 'Chuyển tiền đến BBBTHANGLONG CN Lau Phan Dao Duy Anh (PVComBank Pay) thanh toan don hang 634248107', 'datetime' => '2026-07-05 12:00:00'],
            ['user' => $kien, 'type' => 'loan', 'amount' => 3000000, 'desc' => 'Mua ram, đói kém', 'datetime' => '2026-07-08 12:00:00'],
            ['user' => $kien, 'type' => 'repayment', 'amount' => 3000000, 'desc' => 'Trả nợ mua ram', 'datetime' => '2026-07-11 12:00:00'],
            ['user' => $hung, 'type' => 'contribution', 'amount' => 700000, 'desc' => 'Tán lộc', 'datetime' => '2026-07-12 12:00:00'],
            ['user' => $minh, 'type' => 'expense', 'amount' => 150000, 'desc' => 'mua kìm đấu wifi', 'datetime' => '2026-07-20 12:00:00'],
            ['user' => $kien, 'type' => 'expense', 'amount' => 535000, 'desc' => 'Quỹ networking với anh 3T - Tri ân vi da den', 'datetime' => '2026-07-29 12:00:00'],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 900000, 'desc' => 'CTO góp cns tháng 7', 'datetime' => '2026-07-29 18:00:00'],
            ['user' => $viet, 'type' => 'contribution', 'amount' => 200000, 'desc' => 'Trả nợ giúp em Phúc Đăng 200k tiền cát bà, còn lại nợ 500k', 'datetime' => '2026-08-01 10:00:00'],
            ['user' => $minh, 'type' => 'expense', 'amount' => 100000, 'desc' => 'tiền ăn chè', 'datetime' => '2026-08-04 12:00:00'],
        ];

        foreach ($transactionsData as $item) {
            Transaction::create([
                'fund_id' => $fund->id,
                'user_id' => $item['user']->id,
                'type' => $item['type'],
                'amount' => $item['amount'],
                'description' => $item['desc'],
                'status' => 'approved',
                'approved_by' => $admin->id,
                'created_at' => Carbon::parse($item['datetime']),
            ]);
        }

        // Run ProjectSeeder to seed projects and members
        $this->call(ProjectSeeder::class);
    }
}
