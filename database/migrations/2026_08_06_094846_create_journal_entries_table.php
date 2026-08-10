<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_account_id')->constrained('accounts'); // Nguồn tiền
            $table->foreignId('to_account_id')->constrained('accounts');   // Đích đến
            $table->decimal('amount', 15, 2)->comment('Số tiền hạch toán');
            $table->text('memo')->nullable()->comment('Diễn giải chi tiết dòng');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
