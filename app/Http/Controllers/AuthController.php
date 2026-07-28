<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'))->with('success', 'Đăng nhập thành công! Chào mừng ' . Auth::user()->name);
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $avatarInitials = strtoupper(substr($request->name, 0, 2));

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'member',
            'avatar' => $avatarInitials,
            'share_percentage' => 0.00,
            'current_debt' => 0.00,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Tạo tài khoản thành công! Chào mừng bạn gia nhập team.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Đã đăng xuất tài khoản an toàn.');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Địa chỉ email này chưa được đăng ký trong hệ thống.']);
        }

        // Demo simulation message for local environment
        return back()->with('success', 'Link đặt lại mật khẩu đã được gửi đến email của bạn (Trong môi trường demo, bạn có thể đăng nhập bằng tài khoản mẫu hoặc tạo tài khoản mới).');
    }
}
