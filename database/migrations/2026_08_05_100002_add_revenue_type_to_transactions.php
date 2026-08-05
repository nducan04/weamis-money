<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add revenue_type enum column
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('revenue_type', ['development', 'subscription'])->nullable()->after('billing_cycle');
        });

        // 2. Set existing project-linked transactions to 'development'
        DB::statement("UPDATE transactions SET revenue_type = 'development' WHERE project_id IS NOT NULL AND revenue_type IS NULL");
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('revenue_type');
        });
    }
};
