<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('verify:networth', function () {
    $f = \App\Models\Fund::first();
    $this->info("Fund balance: {$f->balance}, total_profit: {$f->total_profit}");
    $this->info("Transaction count: " . \App\Models\Transaction::count());

    $members = \App\Models\User::where('role', '!=', 'admin')->get();
    foreach ($members as $m) {
        $txs = \App\Models\Transaction::where('user_id', $m->id)->where('status', 'approved')->get();
        $c = $txs->whereIn('type', ['contribution', 'repayment', 'profit'])->sum('amount');
        $w = $txs->whereIn('type', ['expense', 'loan', 'withdrawal'])->sum('amount');
        $nw = $c - $w;
        $this->line("{$m->name} | Góp: " . number_format($c) . " | Rút/Vay: " . number_format($w) . " | Net Worth: " . number_format($nw));
    }
});
