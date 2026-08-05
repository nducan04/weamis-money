<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $mapping = [
            'Nguyễn Hoàng Việt'      => 'nhv',
            'Hồ Trùng Sơn'           => 'hts',
            'Nguyễn Quý Đức'         => 'nqd',
            'Nguyễn Đăng Phúc Hưng'  => 'ndph',
            'Nguyễn Trung Kiên'      => 'ntk',
            'Vũ Đức Hoàng Anh'       => 'vdha',
            'Lê Văn Thành An'        => 'lvta',
            'Trịnh Quang Minh'       => 'tqm',
            'Nguyễn Đức An'          => 'nda',
        ];

        foreach ($mapping as $name => $username) {
            $user = User::where('name', $name)->first();
            if ($user) {
                $user->update([
                    'username' => $username,
                    'password' => Hash::make('1234'),
                ]);
            }
        }

        // Reset admin password
        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            $admin->update([
                'username' => 'admin',
                'password' => Hash::make('1322'),
            ]);
        }
    }

    public function down(): void
    {
        // No-op
    }
};
