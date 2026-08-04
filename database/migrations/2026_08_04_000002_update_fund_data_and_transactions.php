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

        // 5. Ensure User Nguyễn Đức An exists
        $nda = User::firstOrCreate(
            ['username' => 'nda'],
            [
                'name' => 'Nguyễn Đức An',
                'email' => 'an.nd@weamis.com',
                'password' => \Illuminate\Support\Facades\Hash::make('1234'),
                'role' => 'member',
                'avatar' => 'DA',
                'share_percentage' => 0.00,
                'current_debt' => 0.00,
            ]
        );

        // 6. Ensure Project WM (Weamis Money) exists
        $wmLeadId = $nda->id;
        $wm = \App\Models\Project::firstOrCreate(
            ['code' => 'WM'],
            [
                'name' => 'Weamis Money',
                'description' => 'Quản lý tiền Weamis',
                'release_date' => '2026-07-31',
                'weamis_fund_percentage' => 0.00,
                'lead_user_id' => $wmLeadId,
                'created_by_user_id' => $wmLeadId,
                'status' => 'active',
            ]
        );
        \App\Models\ProjectMember::firstOrCreate(
            ['project_id' => $wm->id, 'user_id' => $nda->id],
            ['share_percentage' => 100.00]
        );

        // 7. Ensure transactions attached to W-LALOT and W-EB
        $wLalot = \App\Models\Project::where('code', 'W-LALOT')->first();
        if ($wLalot) {
            $txLalot = Transaction::where('description', 'like', '%Wifi lalot%')->where('amount', 3250000)->first();
            if ($txLalot) {
                $txLalot->project_id = $wLalot->id;
                $txLalot->save();
            }
        }

        $wEb = \App\Models\Project::where('code', 'W-EB')->first();
        if ($wEb) {
            $txEb = Transaction::where('amount', 3250000)
                ->where('description', 'Góp vào quỹ chung')
                ->first();
            if ($txEb) {
                $txEb->project_id = $wEb->id;
                $txEb->save();
            }
        }
    }

    public function down(): void
    {
        // No-op
    }
};
