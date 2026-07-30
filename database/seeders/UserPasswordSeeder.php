<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserPasswordSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('weamis123');

        $users = User::all();
        foreach ($users as $u) {
            if (empty($u->password)) {
                $u->password = $defaultPassword;
                $u->save();
            }
        }

        // Ensure Nguyễn Hoàng Việt has admin role
        $admin = User::where('name', 'LIKE', '%Việt%')->first() ?? User::first();
        if ($admin) {
            $admin->role = 'admin';
            $admin->password = $defaultPassword;
            $admin->save();
        }
    }
}
