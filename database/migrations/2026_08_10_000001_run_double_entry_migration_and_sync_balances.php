<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use App\Models\Fund;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Run the custom command to migrate historical transactions to double-entry
        Artisan::call('app:migrate-to-double-entry');

        // 2. Run syncBalance to correct any stale fund balance on production
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
