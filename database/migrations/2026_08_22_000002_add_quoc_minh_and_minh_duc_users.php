<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Account;

return new class extends Migration
{
    public function up(): void
    {
        $defaultPassword = Hash::make('1234');

        // Create Quốc Minh
        $quocMinh = User::firstOrCreate(
            ['username' => 'qm'],
            [
                'name' => 'Quốc Minh',
                'email' => 'quocminh@weamis.com',
                'password' => $defaultPassword,
                'role' => 'member',
                'share_percentage' => 0,
                'current_debt' => 248733,
                'avatar' => 'QM',
            ]
        );

        Account::firstOrCreate(
            ['type' => 'user', 'owner_type' => User::class, 'owner_id' => $quocMinh->id],
            ['name' => 'Ví Quốc Minh', 'balance' => -248733]
        );

        // Create Minh Đức
        $minhDuc = User::firstOrCreate(
            ['username' => 'md'],
            [
                'name' => 'Minh Đức',
                'email' => 'minhduc@weamis.com',
                'password' => $defaultPassword,
                'role' => 'member',
                'share_percentage' => 0,
                'current_debt' => 0,
                'avatar' => 'MĐ',
            ]
        );

        Account::firstOrCreate(
            ['type' => 'user', 'owner_type' => User::class, 'owner_id' => $minhDuc->id],
            ['name' => 'Ví Minh Đức', 'balance' => 1267]
        );
    }

    public function down(): void
    {
    }
};
