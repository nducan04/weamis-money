<?php

namespace App\Services\Audit;

use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ReconciliationService
{
    /**
     * @return array{is_clean: bool, drifts: array}
     */
    public function reconcile(bool $fix = false): array
    {
        $accounts = Account::all();
        $drifts = [];

        foreach ($accounts as $acc) {
            $in = JournalEntry::where('to_account_id', $acc->id)->sum('amount');
            $out = JournalEntry::where('from_account_id', $acc->id)->sum('amount');
            
            $computed = round((float)$in - (float)$out, 2);
            $stored = round((float)$acc->balance, 2);

            if ($computed !== $stored) {
                $driftAmount = $computed - $stored;
                $drifts[] = [
                    'account_id' => $acc->id,
                    'type' => $acc->type,
                    'name' => $acc->name,
                    'computed' => $computed,
                    'stored' => $stored,
                    'diff' => $driftAmount,
                ];

                if ($fix) {
                    $acc->balance = $computed;
                    $acc->save();
                }
            }
        }

        $isClean = empty($drifts);

        if (!$isClean && !$fix) {
            $this->alertDrifts($drifts);
        }

        return [
            'is_clean' => $isClean,
            'drifts' => $drifts,
        ];
    }

    protected function alertDrifts(array $drifts): void
    {
        $message = "⚠️ *Hermes-Sentinel: Data Drift Detected!*\n";
        foreach ($drifts as $d) {
            $message .= sprintf(
                "- Acc %d (%s): Computed %s vs Stored %s (Diff: %s)\n",
                $d['account_id'],
                $d['name'],
                number_format($d['computed']),
                number_format($d['stored']),
                number_format($d['diff'])
            );
        }

        Log::warning("Hermes-Sentinel Drift: " . json_encode($drifts));

        $token = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');
        
        if ($token && $chatId) {
            try {
                Http::timeout(3)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send Telegram alert: " . $e->getMessage());
            }
        }
    }
}
