<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix transaction 12 if it's currently marked as loan with salary description using raw DB query to avoid model soft deletes
        $tx12 = DB::table('transactions')->where('id', 12)->first();
        if ($tx12 && $tx12->type === 'loan') {
            DB::table('transactions')->where('id', 12)->update(['type' => 'withdrawal']);

            // Revert debt deduction from user for this false loan
            $user = DB::table('users')->where('id', $tx12->user_id)->first();
            if ($user && $user->current_debt >= $tx12->amount) {
                DB::table('users')->where('id', $user->id)->decrement('current_debt', $tx12->amount);
            }
        }
    }

    public function down(): void
    {
        $tx12 = DB::table('transactions')->where('id', 12)->first();
        if ($tx12 && $tx12->type === 'withdrawal') {
            DB::table('transactions')->where('id', 12)->update(['type' => 'loan']);

            $user = DB::table('users')->where('id', $tx12->user_id)->first();
            if ($user) {
                DB::table('users')->where('id', $user->id)->increment('current_debt', $tx12->amount);
            }
        }
    }
};
