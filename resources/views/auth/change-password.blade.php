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
                🔒
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Đổi Mật Khẩu</h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 font-medium">Cập nhật mật khẩu bảo vệ cho tài khoản: <span class="font-extrabold text-emerald-600 dark:text-emerald-400">{{ Auth::user()->name }}</span></p>
        </div>

        <!-- Change Password Form -->
        <form action="{{ route('password.updateCurrent') }}" method="POST" class="space-y-5 relative z-10">
            @csrf

            <!-- Current Password -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Mật Khẩu Hiện Tại</label>
                <input type="password" name="current_password" required autofocus placeholder="••••••••" class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700/80 text-sm font-semibold focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:text-white transition shadow-sm">
                @error('current_password')
                    <p class="text-xs text-rose-500 font-bold mt-1.5 flex items-center space-x-1">
                        <span>⚠️</span>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <!-- New Password -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Mật Khẩu Mới</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700/80 text-sm font-semibold focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:text-white transition shadow-sm">
                @error('password')
                    <p class="text-xs text-rose-500 font-bold mt-1.5 flex items-center space-x-1">
                        <span>⚠️</span>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <!-- Password Confirmation -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Xác Nhận Mật Khẩu Mới</label>
                <input type="password" name="password_confirmation" required placeholder="••••••••" class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700/80 text-sm font-semibold focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:text-white transition shadow-sm">
            </div>

            <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-sm rounded-2xl shadow-lg shadow-emerald-500/25 active:scale-95 transition duration-200">
                Lưu Mật Khẩu Mới →
            </button>
        </form>

        <div class="mt-8 text-center text-xs text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-700/80 pt-5 relative z-10 font-medium">
            Quay lại 
            <a href="{{ route('dashboard') }}" class="font-extrabold text-emerald-600 dark:text-emerald-400 hover:underline">Trang chủ Quỹ</a>
        </div>
    </div>
</div>
@endsection
