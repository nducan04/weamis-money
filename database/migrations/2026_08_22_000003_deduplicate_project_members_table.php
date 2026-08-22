<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicate (project_id, user_id) records in project_members table
        $duplicates = DB::table('project_members')
            ->select('project_id', 'user_id', DB::raw('MAX(id) as max_id'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('project_id', 'user_id')
            ->having('cnt', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('project_members')
                ->where('project_id', $dup->project_id)
                ->where('user_id', $dup->user_id)
                ->where('id', '!=', $dup->max_id)
                ->delete();
        }
    }

    public function down(): void
    {
    }
};
