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
        $request->validate([
            'email' => ['required'],
            'password' => ['required'],
        ]);

        $loginInput = strtolower(trim($request->email));
        $remember = $request->has('remember');

        if (Auth::attempt(['email' => $loginInput, 'password' => $request->password], $remember) ||
            Auth::attempt(['name' => $loginInput, 'password' => $request->password], $remember)) {
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

        return redirect()->route('login')->with('success', 'Đã đăng xuất tài khoản.');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'account' => ['required', 'string'],
        ], [
            'account.required' => 'Vui lòng nhập tên tài khoản hoặc email.',
        ]);

        $account = strtolower(trim($request->account));

        $user = User::where('email', $account)->orWhere('name', $account)->first();

        if (!$user) {
            return back()->withErrors(['account' => 'Tài khoản hoặc email này không tồn tại trong hệ thống.'])->withInput();
        }

        session(['reset_user_id' => $user->id]);

        return redirect()->route('password.reset')->with('info', 'Xác thực tài khoản thành công! Vui lòng nhập mật khẩu mới.');
    }

    public function showResetPassword()
    {
        $userId = session('reset_user_id');

        if (!$userId) {
            return redirect()->route('password.request')->withErrors(['account' => 'Vui lòng nhập tài khoản cần khôi phục mật khẩu.']);
        }

        $user = User::findOrFail($userId);

        return view('auth.reset-password', compact('user'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ], [
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu tối thiểu 4 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->password = Hash::make($request->password);
        $user->save();

        session()->forget('reset_user_id');

        return redirect()->route('login')->with('success', 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập bằng mật khẩu mới.');
    }

    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:4', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới tối thiểu 4 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'password.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không chính xác.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Đổi mật khẩu thành công!');
    }
}
