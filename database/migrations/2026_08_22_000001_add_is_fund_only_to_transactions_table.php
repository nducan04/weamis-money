<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('is_fund_only')->default(false)->after('status');
        });

        // Set is_fund_only = true for transaction #179 ("em Minh Đức trả lẩu (trả nợ quỹ)")
        DB::table('transactions')
            ->where('id', 179)
            ->orWhere('description', 'like', '%Minh Đức trả lẩu%')
            ->update(['is_fund_only' => true]);
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('is_fund_only');
        });
    }
};
