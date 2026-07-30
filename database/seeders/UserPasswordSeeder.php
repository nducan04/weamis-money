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
        // Get initial passwords from environment configuration
        $adminPassword = env('ADMIN_INITIAL_PASSWORD', '1322');
        $defaultMemberPassword = env('DEFAULT_MEMBER_PASSWORD', '1234');

        // 1. Create or update the SOLE Admin account: admin / 1322
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
                'name' => 'Quản Trị Viên (Admin)',
                'username' => 'admin',
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
            ]);
        }

        // 2. All other team members are assigned role 'member' and short initial usernames & password '1234'
        $users = User::where('id', '!=', $admin->id)->get();
        $usedUsernames = ['admin'];

        foreach ($users as $u) {
            if ($u->name === 'Nguyễn Hoàng Việt') {
                $username = 'nhv';
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

            // Enforce role = 'member' for all non-admin users
            $u->update([
                'username' => $username,
                'password' => Hash::make($defaultMemberPassword),
                'role' => 'member',
            ]);
        }
    }
}
