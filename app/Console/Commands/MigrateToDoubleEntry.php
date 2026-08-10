<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateToDoubleEntry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-to-double-entry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration to Double-Entry Bookkeeping...');

        // 1. Create External Account
        $externalAcc = \App\Models\Account::firstOrCreate(
            ['type' => 'external', 'name' => 'Nguồn tiền bên ngoài (Khách hàng / Đối tác)']
        );

        // 2. Create Fund Account
        $fund = \App\Models\Fund::first();
        if ($fund) {
            $fundAcc = \App\Models\Account::firstOrCreate(
                ['type' => 'fund', 'owner_type' => get_class($fund), 'owner_id' => $fund->id],
                ['name' => 'Quỹ ' . $fund->name, 'balance' => 0]
            );
        }

        // 3. Create Project Accounts
        $projects = \App\Models\Project::withTrashed()->get();
        foreach ($projects as $project) {
            \App\Models\Account::firstOrCreate(
                ['type' => 'project', 'owner_type' => get_class($project), 'owner_id' => $project->id],
                ['name' => 'Dự án ' . $project->name, 'balance' => 0]
            );
        }

        // 4. Create User Accounts
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            \App\Models\Account::firstOrCreate(
                ['type' => 'user', 'owner_type' => get_class($user), 'owner_id' => $user->id],
                ['name' => 'Ví ' . $user->name, 'balance' => 0]
            );
        }

        $this->info('Accounts created.');

        // 5. Migrate Transactions
        $transactions = \App\Models\Transaction::withTrashed()->get();
        $this->info("Found {$transactions->count()} transactions to migrate.");

        foreach ($transactions as $tx) {
            // Skip if already migrated
            if (\App\Models\JournalEntry::where('transaction_id', $tx->id)->exists()) {
                continue;
            }

            $userAcc = \App\Models\Account::where('type', 'user')->where('owner_id', $tx->user_id)->first();
            $fundAcc = \App\Models\Account::where('type', 'fund')->first();
            $projectAcc = null;
            if ($tx->project_id) {
                $projectAcc = \App\Models\Account::where('type', 'project')->where('owner_id', $tx->project_id)->first();
            }

            $fromAccId = null;
            $toAccId = null;

            // Logic based on type:
            switch ($tx->type) {
                case 'contribution':
                case 'repayment':
                    // User -> Fund
                    $fromAccId = $userAcc->id;
                    $toAccId = $fundAcc->id;
                    break;
                case 'loan':
                case 'withdrawal':
                case 'distribution':
                    // Fund -> User
                    $fromAccId = $fundAcc->id;
                    $toAccId = $userAcc->id;
                    break;
                case 'profit':
                    // External/Project -> User or Fund
                    // Previously treated as user contribution. Let's make it External -> Fund
                    $fromAccId = $externalAcc->id;
                    $toAccId = $fundAcc->id; // Actually, profit increases networth. In AnalyticsController, it's summed into contributions of User.
                    // If it's summed into User's contributions, it means it's treated exactly like a contribution!
                    $fromAccId = $userAcc->id;
                    $toAccId = $fundAcc->id;
                    break;
                case 'expense':
                    // Fund -> User (User withdraws to pay expense, reducing their equity)
                    $fromAccId = $fundAcc->id;
                    $toAccId = $userAcc->id;
                    break;
                default:
                    // Unknown, default External -> Fund
                    $fromAccId = $externalAcc->id;
                    $toAccId = $fundAcc->id;
            }

            // If there's a project involved, maybe route it through project?
            // Since the old system didn't transfer money to Project wallets explicitly,
            // we will stick to the basic User <-> Fund mapping to preserve balances,
            // but log it to JournalEntry.

            $je = new \App\Models\JournalEntry();
            $je->transaction_id = $tx->id;
            $je->from_account_id = $fromAccId;
            $je->to_account_id = $toAccId;
            $je->amount = $tx->amount;
            $je->memo = 'Migrated: ' . $tx->type;
            if ($tx->trashed()) {
                $je->deleted_at = $tx->deleted_at;
            }
            $je->save();
        }

        // 6. Recalculate Balances
        $accounts = \App\Models\Account::all();
        foreach ($accounts as $acc) {
            $totalIn = \App\Models\JournalEntry::where('to_account_id', $acc->id)->whereNull('deleted_at')->sum('amount');
            $totalOut = \App\Models\JournalEntry::where('from_account_id', $acc->id)->whereNull('deleted_at')->sum('amount');
            
            if ($acc->type === 'user') {
                // User account represents Equity (Vốn chủ sở hữu).
                // from_account = User -> means User gave money to Fund -> Equity increases.
                // to_account = User -> means Fund gave money back -> Equity decreases.
                $acc->balance = $totalOut - $totalIn;
            } else {
                // Fund, Project, External represent Assets/Sinks.
                // to_account = Fund -> means Fund received money -> Asset increases.
                $acc->balance = $totalIn - $totalOut;
            }
            $acc->save();
        }

        $this->info('Migration completed successfully.');
    }
}
