<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        // Fix transaction 12 if it's currently marked as loan with salary description
        $tx12 = Transaction::find(12);
        if ($tx12 && $tx12->type === 'loan') {
            $tx12->type = 'withdrawal';
            $tx12->save();

            // Revert debt deduction from user for this false loan
            $user = User::find($tx12->user_id);
            if ($user && $user->current_debt >= $tx12->amount) {
                $user->decrement('current_debt', $tx12->amount);
            }
        }
    }

    public function down(): void
    {
        $tx12 = Transaction::find(12);
        if ($tx12 && $tx12->type === 'withdrawal') {
            $tx12->type = 'loan';
            $tx12->save();

            $user = User::find($tx12->user_id);
            if ($user) {
                $user->increment('current_debt', $tx12->amount);
            }
        }
    }
};
