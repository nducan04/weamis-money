<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Transaction;
use App\Models\Fund;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Delete artificial Túi Thần Tài transaction
        Transaction::where('description', 'like', '%Túi Thần Tài%')->delete();

        // 2. Reset total_profit to 0 on Fund
        $fund = Fund::first();
        if ($fund) {
            $fund->update(['total_profit' => 0.00]);
        }
    }

    public function down(): void
    {
        // No-op
    }
};
