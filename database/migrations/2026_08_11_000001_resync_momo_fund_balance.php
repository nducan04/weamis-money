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

        // 1. Remove ALL old Momo adjustment transactions (including soft-deleted ones)
        $oldTxs = Transaction::withTrashed()->where('description', 'like', '%Momo%')->get();
        foreach ($oldTxs as $tx) {
            JournalEntry::where('transaction_id', $tx->id)->forceDelete();
            $tx->forceDelete();
        }

        // 2. Calculate dynamic adjustment needed to reach 7,135,340đ
        $activeTxs = Transaction::where('status', 'approved')
            ->where('description', 'not like', '%Momo%')
            ->get();

        $contrib = $activeTxs->where('type', 'contribution')->sum('amount');
        $repay = $activeTxs->where('type', 'repayment')->sum('amount');
        $profit = $activeTxs->where('type', 'profit')->sum('amount');
        $adjust = $activeTxs->where('type', 'adjustment')->sum('amount');
        $expense = $activeTxs->where('type', 'expense')->sum('amount');
        $loan = $activeTxs->where('type', 'loan')->sum('amount');
        $withdraw = $activeTxs->where('type', 'withdrawal')->sum('amount');
        $distrib = $activeTxs->where('type', 'distribution')->sum('amount');

        $baseSum = ($contrib + $repay + $adjust + $profit) - ($expense + $loan + $withdraw + $distrib);
        $targetMomo = 7135340;
        $diffNeeded = $targetMomo - $baseSum;

        if (abs($diffNeeded) > 0.01) {
            $txType = $diffNeeded > 0 ? 'adjustment' : 'expense';
            $amount = abs($diffNeeded);

            $tx = Transaction::create([
                'fund_id' => $fund->id,
                'user_id' => $admin->id,
                'type' => $txType,
                'amount' => $amount,
                'description' => 'Điều chỉnh số dư khớp thực tế ví Momo',
                'status' => 'approved',
                'approved_by' => $admin->id,
                'created_at' => '2026-01-01 00:00:00',
            ]);

            $fundAcc = Account::where('type', 'fund')->first();
            $externalAcc = Account::where('type', 'external')->first();

            if ($fundAcc && $externalAcc) {
                $fromId = $txType === 'adjustment' ? $externalAcc->id : $fundAcc->id;
                $toId = $txType === 'adjustment' ? $fundAcc->id : $externalAcc->id;

                JournalEntry::create([
                    'transaction_id' => $tx->id,
                    'from_account_id' => $fromId,
                    'to_account_id' => $toId,
                    'amount' => $amount,
                    'memo' => $txType . ': Điều chỉnh số dư khớp thực tế ví Momo',
                ]);
            }
        }

        // 3. Sync Fund Balance to real Momo (7,135,340đ)
        Fund::syncBalance();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
