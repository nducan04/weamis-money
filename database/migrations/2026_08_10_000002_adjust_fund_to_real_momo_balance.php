<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Transaction;
use App\Models\Fund;
use App\Models\User;
use App\Models\Account;
use App\Models\JournalEntry;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $fund = Fund::first();

        if (!$admin || !$fund) return;

        // 1. Ensure any old test adjustment is removed
        Transaction::where('description', 'like', '%Điều chỉnh chênh lệch thực tế Momo%')->delete();

        // 2. Check if the adjustment transaction already exists
        $existing = Transaction::where('description', 'Điều chỉnh số dư khớp thực tế ví Momo')->first();
        
        if (!$existing) {
            $tx = Transaction::create([
                'fund_id' => $fund->id,
                'user_id' => $admin->id,
                'type' => 'expense',
                'amount' => 2866606,
                'description' => 'Điều chỉnh số dư khớp thực tế ví Momo',
                'status' => 'approved',
                'approved_by' => $admin->id,
                'created_at' => '2026-01-01 00:00:00',
            ]);

            // Create Journal Entry for Double Entry Bookkeeping
            $fundAcc = Account::where('type', 'fund')->first();
            $externalAcc = Account::where('type', 'external')->first();

            if ($fundAcc && $externalAcc) {
                JournalEntry::create([
                    'transaction_id' => $tx->id,
                    'from_account_id' => $fundAcc->id,
                    'to_account_id' => $externalAcc->id,
                    'amount' => 2866606,
                    'memo' => 'expense: Điều chỉnh số dư khớp thực tế ví Momo',
                ]);
            }
        }

        // 3. Sync Fund Balance to real Momo
        Fund::syncBalance();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Transaction::where('description', 'Điều chỉnh số dư khớp thực tế ví Momo')->forceDelete();
        Fund::syncBalance();
    }
};
