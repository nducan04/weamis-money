<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('claimant_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('evidence_type', ['file', 'link', 'text', 'none'])->default('none');
            $table->text('evidence_value')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['responsible_user_id']);
            $table->dropForeign(['claimant_user_id']);
            $table->dropColumn(['project_id', 'responsible_user_id', 'claimant_user_id', 'evidence_type', 'evidence_value']);
        });
    }
};
