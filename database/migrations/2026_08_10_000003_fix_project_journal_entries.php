<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\JournalEntry;
use App\Models\Account;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure project accounts exist for all projects
        foreach (Project::all() as $p) {
            Account::firstOrCreate(
                ['type' => 'project', 'owner_type' => Project::class, 'owner_id' => $p->id],
                ['name' => 'Dự án ' . $p->name, 'balance' => 0]
            );
        }

        // 2. Fix JournalEntries for all transactions with project_id
        $txs = Transaction::whereNotNull('project_id')->get();
        foreach ($txs as $tx) {
            $projectAcc = Account::where('type', 'project')->where('owner_id', $tx->project_id)->first();
            $userAcc = Account::where('type', 'user')->where('owner_id', $tx->user_id)->first();
            $fundAcc = Account::where('type', 'fund')->first();
            
            if (!$projectAcc) continue;

            $jes = JournalEntry::where('transaction_id', $tx->id)->get();
            
            if ($jes->count() <= 1) {
                $fromId = ($tx->type === 'expense') ? $projectAcc->id : ($userAcc ? $userAcc->id : $fundAcc->id);
                $toId = ($tx->type === 'expense') ? ($userAcc ? $userAcc->id : $fundAcc->id) : $projectAcc->id;
                
                if ($jes->count() === 1) {
                    $jes->first()->update([
                        'from_account_id' => $fromId,
                        'to_account_id' => $toId,
                    ]);
                } else {
                    JournalEntry::create([
                        'transaction_id' => $tx->id,
                        'from_account_id' => $fromId,
                        'to_account_id' => $toId,
                        'amount' => $tx->amount,
                        'memo' => $tx->type . ': ' . $tx->description,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
