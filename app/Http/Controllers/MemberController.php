<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function index()
    {
        if (!auth()->user()?->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Chỉ tài khoản Admin mới có quyền truy cập trang Quản lý thành viên.');
        }

        $members = User::orderBy('name')->get();
        return view('admin.members.index', compact('members'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()?->isAdmin()) {
            return redirect()->back()->with('error', 'Bạn không có quyền thực hiện thao tác này.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username|alpha_dash',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:4',
            'role' => 'required|in:admin,member',
            'share_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $initials = strtoupper(substr(trim($validated['name']), 0, 2));

        User::create([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'avatar' => $initials,
            'share_percentage' => $validated['share_percentage'],
            'current_debt' => 0,
        ]);

        return redirect()->back()->with('success', 'Đã tạo tài khoản thành viên mới cho ' . $validated['name']);
    }

    public function update(Request $request, User $user)
    {
        if (!auth()->user()?->isAdmin()) {
            return redirect()->back()->with('error', 'Bạn không có quyền thực hiện thao tác này.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|alpha_dash|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,member',
            'share_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $user->update([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => $validated['email'],
            'role' => $validated['role'],
            'share_percentage' => $validated['share_percentage'],
        ]);

        return redirect()->back()->with('success', 'Đã cập nhật thông tin tài khoản ' . $user->name);
    }

    public function resetPassword(Request $request, User $user)
    {
        if (!auth()->user()?->isAdmin()) {
            return redirect()->back()->with('error', 'Bạn không có quyền thực hiện thao tác này.');
        }

        $newPassword = $request->input('password', '1234');
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return redirect()->back()->with('success', 'Đã reset mật khẩu tài khoản ' . $user->name . ' về: ' . $newPassword);
    }

    public function destroy(User $user)
    {
        if (!auth()->user()?->isAdmin()) {
            return redirect()->back()->with('error', 'Bạn không có quyền thực hiện thao tác này.');
        }

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Bạn không thể tự xóa tài khoản của chính mình.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->back()->with('success', 'Đã xóa tài khoản ' . $name);
    }

    public function updateShare(Request $request, User $user)
    {
        if (!auth()->user()?->isAdmin()) {
            return redirect()->back()->with('error', 'Chỉ Admin mới có quyền chỉnh sửa % cổ phần của từng thành viên.');
        }

        $validated = $request->validate([
            'share_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $user->update([
            'share_percentage' => $validated['share_percentage'],
        ]);

        return redirect()->back()->with('success', 'Đã cập nhật tỷ lệ % cổ phần cho ' . $user->name);
    }
}
