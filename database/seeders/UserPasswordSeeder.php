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
        $adminPassword = env('ADMIN_INITIAL_PASSWORD', '1322');
        $defaultMemberPassword = env('DEFAULT_MEMBER_PASSWORD', '1234');

        // 1. Super Admin Account: admin / 1322
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

        // 2. Ensure Nguyễn Hoàng Việt is a distinct member account: nhv / 1234
        $viet = User::where('name', 'Nguyễn Hoàng Việt')->first();
        if (!$viet) {
            User::create([
                'name' => 'Nguyễn Hoàng Việt',
                'username' => 'nhv',
                'email' => 'viet.nh@weamis.com',
                'password' => Hash::make($defaultMemberPassword),
                'role' => 'member',
                'share_percentage' => 25.00,
                'current_debt' => 0.00,
                'avatar' => 'HV',
            ]);
        } else {
            $viet->update([
                'username' => 'nhv',
                'password' => Hash::make($defaultMemberPassword),
                'role' => 'member',
            ]);
        }

        // 3. Map all other team members to short initial usernames & Bcrypt hashed passwords
        $users = User::whereNotIn('id', [$admin->id, $viet->id])->get();
        $usedUsernames = ['admin', 'nhv'];

        foreach ($users as $u) {
            if ($u->name === 'Hồ Trung Sơn') {
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
                'role' => 'member',
            ]);
        }
    }
}
