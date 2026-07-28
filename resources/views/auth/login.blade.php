@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto my-8 sm:my-16 px-2">
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-9 shadow-2xl border border-slate-100 dark:border-slate-700/80 relative overflow-hidden">
        <!-- Subtle Decorative Background Glow -->
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-teal-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <!-- Logo & Header -->
        <div class="text-center mb-8 relative z-10">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 text-white font-black text-3xl flex items-center justify-center shadow-lg shadow-emerald-500/25 mx-auto mb-3.5 transform hover:scale-105 transition duration-300">
                💲
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Đăng Nhập</h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 font-medium">Quản lý quỹ weamis</p>
        </div>

        <!-- Login Form -->
        <form action="{{ route('login') }}" method="POST" class="space-y-5 relative z-10">
            @csrf

            <!-- Email / Username Field -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Tài khoản / Email</label>
                <input type="text" name="email" value="{{ old('email') }}" required autofocus placeholder="admin" autocomplete="off" class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700/80 text-sm font-semibold focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:text-white transition shadow-sm">
                @error('email')
                    <p class="text-xs text-rose-500 font-bold mt-1.5 flex items-center space-x-1">
                        <span>⚠️</span>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <!-- Password Field -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Mật khẩu</label>
                <input type="password" name="password" required placeholder="••••••••" autocomplete="new-password" class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700/80 text-sm font-semibold focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:text-white transition shadow-sm">
                @error('password')
                    <p class="text-xs text-rose-500 font-bold mt-1.5 flex items-center space-x-1">
                        <span>⚠️</span>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <!-- Checkbox & Forgot Password Row (Aligned) -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center space-x-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 dark:border-slate-600 transition">
                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Ghi nhớ đăng nhập</span>
                </label>

                <a href="{{ route('password.request') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 hover:underline transition">
                    Quên mật khẩu?
                </a>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 active:scale-[0.98] text-white font-black text-sm rounded-2xl shadow-lg shadow-emerald-600/30 transition duration-200 flex items-center justify-center space-x-2">
                <span>Đăng Nhập</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </form>

        <!-- Footer link -->
        <div class="mt-8 pt-5 border-t border-slate-100 dark:border-slate-700/60 text-center text-xs text-slate-500 dark:text-slate-400 font-medium">
            Chưa có tài khoản? 
            <a href="{{ route('register') }}" class="font-extrabold text-emerald-600 dark:text-emerald-400 hover:underline ml-1">Đăng ký tài khoản mới</a>
        </div>
    </div>
</div>
@endsection
