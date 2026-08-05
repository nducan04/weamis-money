<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add effective_from column
        Schema::table('project_members', function (Blueprint $table) {
            $table->date('effective_from')->nullable()->after('share_percentage');
        });

        // 2. Set existing rows effective_from to their created_at date (or project creation date)
        DB::statement("UPDATE project_members SET effective_from = DATE(created_at) WHERE effective_from IS NULL");

        // 3. Make effective_from NOT NULL after backfill
        Schema::table('project_members', function (Blueprint $table) {
            $table->date('effective_from')->nullable(false)->change();
        });

        // 4. Drop old unique constraint and add new one
        Schema::table('project_members', function (Blueprint $table) {
            $table->dropUnique(['project_id', 'user_id']);
            $table->unique(['project_id', 'user_id', 'effective_from'], 'pm_project_user_effective_unique');
        });
    }

    public function down(): void
    {
        Schema::table('project_members', function (Blueprint $table) {
            $table->dropUnique('pm_project_user_effective_unique');
            $table->unique(['project_id', 'user_id']);
            $table->dropColumn('effective_from');
        });
    }
};
