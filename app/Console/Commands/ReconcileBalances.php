<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Audit\ReconciliationService;

class ReconcileBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'money:reconcile {--fix : Auto-fix drifts by updating account balances}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile double-entry ledgers against cached account balances';

    /**
     * Execute the console command.
     */
    public function handle(ReconciliationService $service)
    {
        $this->info("Starting Hermes-Sentinel Internal Audit...");
        $fix = $this->option('fix');

        $result = $service->reconcile($fix);

        if ($result['is_clean']) {
            $this->info("✅ All account balances are perfectly synced with the journal entries!");
            return Command::SUCCESS;
        }

        $this->error("❌ DRIFT DETECTED!");
        
        $headers = ['Account ID', 'Type', 'Name', 'Computed Balance', 'Stored Balance', 'Difference'];
        $rows = array_map(function ($d) {
            return [
                $d['account_id'],
                $d['type'],
                $d['name'],
                number_format($d['computed']),
                number_format($d['stored']),
                number_format($d['diff']),
            ];
        }, $result['drifts']);

        $this->table($headers, $rows);

        if ($fix) {
            $this->info("🔧 '--fix' flag provided. All drifts have been corrected in the database.");
        } else {
            $this->warn("⚠️ Run with '--fix' to overwrite stored balances with computed ones.");
        }

        return Command::FAILURE;
    }
}
