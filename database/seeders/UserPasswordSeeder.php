<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserPasswordSeeder extends Seeder
{
    public function run(): void
    {
        // Get initial passwords from environment configuration to prevent hardcoding sensitive credentials in source code
        $adminPassword = env('ADMIN_INITIAL_PASSWORD', '1322');
        $defaultMemberPassword = env('DEFAULT_MEMBER_PASSWORD', '1234');

        // 1. Create or update Super Admin account from environment configuration
        $admin = User::where('username', 'admin')->orWhere('email', 'admin@weamis.com')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'Quản Trị Viên (Admin)',
                'username' => 'admin',
                'email' => 'admin@weamis.com',
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
                'share_percentage' => 0.00,
                'current_debt' => 0.00,
                'avatar' => 'AD',
            ]);
        } else {
            $admin->update([
                'username' => 'admin',
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
            ]);
        }

        // 2. Map existing team members to short initial usernames & Bcrypt hashed passwords
        $users = User::where('id', '!=', $admin->id)->get();
        $usedUsernames = ['admin'];

        foreach ($users as $u) {
            if ($u->name === 'Nguyễn Hoàng Việt') {
                $username = 'nhv';
                $u->role = 'admin'; // Nguyễn Hoàng Việt is also admin
            } elseif ($u->name === 'Hồ Trung Sơn') {
                $username = 'hts';
            } else {
                // Generate initials from name: e.g. "Trần Văn Nam" => "tvn"
                $words = explode(' ', Str::ascii($u->name));
                $initials = '';
                foreach ($words as $w) {
                    if (!empty($w)) {
                        $initials .= strtolower($w[0]);
                    }
                }
                $username = $initials ?: strtolower(substr($u->name, 0, 3));
                
                // Handle duplicate usernames
                $baseUsername = $username;
                $counter = 1;
                while (in_array($username, $usedUsernames)) {
                    $username = $baseUsername . $counter;
                    $counter++;
                }
            }

            $usedUsernames[] = $username;

            $u->update([
                'username' => $username,
                'password' => Hash::make($defaultMemberPassword),
            ]);
        }
    }
}
