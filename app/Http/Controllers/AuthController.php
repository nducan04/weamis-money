<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        $users = User::orderBy('name')->get();
        return view('auth.login', compact('users'));
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username|alpha_dash',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:4|confirmed',
        ]);

        $initials = strtoupper(substr(trim($validated['name']), 0, 2));

        $user = User::create([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'member',
            'avatar' => $initials,
            'share_percentage' => 0.00,
            'current_debt' => 0.00,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Đăng ký tài khoản thành công! Chào mừng ' . $user->name . ' đến với Weamis Money.');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = trim($request->input('login'));
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $remember = $request->boolean('remember', true);

        if (Auth::attempt([$field => $loginInput, 'password' => $request->password], $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'))->with('success', 'Đăng nhập thành công! Chào mừng ' . Auth::user()->name);
        }

        return redirect()->back()->withErrors([
            'login' => 'Tên đăng nhập hoặc mật khẩu không chính xác.',
        ])->withInput($request->only('login'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dashboard')->with('success', 'Đã đăng xuất thành công. Chuyển về chế độ Khách.');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);
        $user = User::where('email', $request->email)->first();

        // Reset password to default 'weamis123'
        $user->update(['password' => Hash::make('weamis123')]);

        return redirect()->route('login')->with('success', 'Mật khẩu của ' . $user->name . ' đã được khôi phục về mặc định: weamis123');
    }

    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.']);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return redirect()->back()->with('success', 'Đã đổi mật khẩu thành công!');
    }
}
