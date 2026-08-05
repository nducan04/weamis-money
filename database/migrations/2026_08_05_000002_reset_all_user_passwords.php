<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Reset Admin password to 1322
        $admin = User::where('role', 'admin')->orWhere('username', 'admin')->first();
        if ($admin) {
            $admin->update(['password' => Hash::make('1322')]);
        }

        // 2. Reset all member passwords to 1234
        $members = User::where('role', '!=', 'admin')->get();
        foreach ($members as $m) {
            $m->update(['password' => Hash::make('1234')]);
        }
    }

    public function down(): void
    {
        // No-op
    }
};
