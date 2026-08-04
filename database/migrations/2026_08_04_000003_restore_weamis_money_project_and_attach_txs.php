<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ensure User Nguyễn Đức An exists
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

        // 2. Restore Project WM (Weamis Money)
        $wmLeadId = $nda->id;
        $wm = Project::firstOrCreate(
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
        ProjectMember::firstOrCreate(
            ['project_id' => $wm->id, 'user_id' => $nda->id],
            ['share_percentage' => 100.00]
        );

        // 3. Attach 3,250,000đ transactions to W-LALOT and W-EB
        $wLalot = Project::where('code', 'W-LALOT')->first();
        if ($wLalot) {
            $txLalot = Transaction::where('description', 'like', '%Wifi lalot%')->where('amount', 3250000)->first();
            if ($txLalot) {
                $txLalot->project_id = $wLalot->id;
                $txLalot->save();
            }
        }

        $wEb = Project::where('code', 'W-EB')->first();
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
