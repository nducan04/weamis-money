<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Account;
use App\Models\JournalEntry;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Find users
        $nhv = User::where('username', 'nhv')->orWhere('name', 'LIKE', '%Hoàng Việt%')->first();
        $nqd = User::where('username', 'nqd')->orWhere('name', 'LIKE', '%Quý Đức%')->first();
        $admin = User::where('role', 'admin')->first();

        $leadId = $nhv ? $nhv->id : 2;

        // 2. Restore Project CNS
        $cns = Project::firstOrCreate(
            ['code' => 'CNS'],
            [
                'name' => 'Chứng nhận số',
                'description' => 'Dự án Chứng nhận số',
                'release_date' => '2026-08-01',
                'weamis_fund_percentage' => 10.00,
                'lead_user_id' => $leadId,
                'created_by_user_id' => $leadId,
                'status' => 'active',
            ]
        );

        // 3. Restore Members with 75% Viet and 15% NQD (10% Weamis Fund)
        $effectiveDate = '2026-08-01';
        if ($nhv) {
            ProjectMember::firstOrCreate(
                ['project_id' => $cns->id, 'user_id' => $nhv->id, 'effective_from' => $effectiveDate],
                ['share_percentage' => 75.00]
            );
        }

        if ($nqd) {
            ProjectMember::firstOrCreate(
                ['project_id' => $cns->id, 'user_id' => $nqd->id, 'effective_from' => $effectiveDate],
                ['share_percentage' => 15.00]
            );
        }

        // 4. Ensure Project Double-Entry Account exists
        $cnsAccount = Account::firstOrCreate(
            ['type' => 'project', 'owner_type' => Project::class, 'owner_id' => $cns->id],
            ['name' => 'Dự án ' . $cns->name, 'balance' => 2500000]
        );

        // 5. Attach transaction 29/08/2026 09:27 (2,500,000 VND)
        $tx = Transaction::where(function ($q) {
            $q->where('description', 'like', '%cns t8%')
              ->orWhere('description', 'like', '%rate Việt 75 NQD 15%');
        })->where('amount', 2500000)->first();

        if ($tx) {
            $tx->project_id = $cns->id;
            $tx->is_fund_only = false;
            $tx->revenue_type = 'development';
            $tx->save();

            // Link journal entries to project account
            $userAcc = Account::where('type', 'user')->where('owner_id', $tx->user_id)->first();
            if ($userAcc && $cnsAccount) {
                // Delete any split entries if existed, make single entry to project account
                JournalEntry::where('transaction_id', $tx->id)->delete();

                JournalEntry::create([
                    'transaction_id' => $tx->id,
                    'from_account_id' => $userAcc->id,
                    'to_account_id' => $cnsAccount->id,
                    'amount' => $tx->amount,
                    'memo' => 'Dự án Chứng nhận số: ' . $tx->description,
                ]);

                // Recalculate balances
                $cnsAccount->balance = JournalEntry::where('to_account_id', $cnsAccount->id)->sum('amount') 
                                     - JournalEntry::where('from_account_id', $cnsAccount->id)->sum('amount');
                $cnsAccount->save();

                $userAcc->balance = JournalEntry::where('to_account_id', $userAcc->id)->sum('amount') 
                                  - JournalEntry::where('from_account_id', $userAcc->id)->sum('amount');
                $userAcc->save();

                if ($nqd) {
                    $nqdAcc = Account::where('type', 'user')->where('owner_id', $nqd->id)->first();
                    if ($nqdAcc) {
                        $nqdAcc->balance = JournalEntry::where('to_account_id', $nqdAcc->id)->sum('amount') 
                                         - JournalEntry::where('from_account_id', $nqdAcc->id)->sum('amount');
                        $nqdAcc->save();
                    }
                }
            }
        }
    }

    public function down(): void
    {
        $cns = Project::where('code', 'CNS')->first();
        if ($cns) {
            Transaction::where('project_id', $cns->id)->update(['project_id' => null]);
            ProjectMember::where('project_id', $cns->id)->delete();
            Account::where('type', 'project')->where('owner_id', $cns->id)->delete();
            $cns->delete();
        }
    }
};
