<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    public function updateShare(Request $request, User $user)
    {
        $validated = $request->validate([
            'share_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $user->update([
            'share_percentage' => $validated['share_percentage'],
        ]);

        return redirect()->back()->with('success', 'Đã cập nhật tỷ lệ % cổ phần cho ' . $user->name);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'share_percentage' => 'required|numeric|min:0|max:100',
            'avatar' => 'nullable|string|max:4',
        ]);

        $initials = $validated['avatar'] ?? strtoupper(substr($validated['name'], 0, 2));

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make('password'),
            'role' => 'member',
            'avatar' => $initials,
            'share_percentage' => $validated['share_percentage'],
            'current_debt' => 0,
        ]);

        return redirect()->back()->with('success', 'Đã thêm thành viên mới thành công!');
    }
}
