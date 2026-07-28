@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto my-8 sm:my-16 px-2">
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-9 shadow-2xl border border-slate-100 dark:border-slate-700/80 relative overflow-hidden">
        <!-- Subtle Decorative Background Glow -->
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <!-- Logo & Header -->
        <div class="text-center mb-8 relative z-10">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-400 text-white font-black text-3xl flex items-center justify-center shadow-lg shadow-amber-500/25 mx-auto mb-3.5 transform hover:scale-105 transition duration-300">
                🔐
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Quên Mật Khẩu</h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 font-medium">Nhập tên tài khoản hoặc email để xác thực và tạo mật khẩu mới</p>
        </div>

        <!-- Forgot Password Form -->
        <form action="{{ route('password.email') }}" method="POST" class="space-y-5 relative z-10">
            @csrf

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Tài khoản / Email</label>
                <input type="text" name="account" value="{{ old('account') }}" required autofocus placeholder="admin" class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700/80 text-sm font-semibold focus:ring-2 focus:ring-amber-500 focus:border-amber-500 dark:text-white transition shadow-sm">
                @error('account')
                    <p class="text-xs text-rose-500 font-bold mt-1.5 flex items-center space-x-1">
                        <span>⚠️</span>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-black text-sm rounded-2xl shadow-lg shadow-amber-500/25 active:scale-95 transition duration-200">
                Xác Nhận & Tiếp Tục →
            </button>
        </form>

        <div class="mt-8 text-center text-xs text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-700/80 pt-5 relative z-10 font-medium">
            Quay lại 
            <a href="{{ route('login') }}" class="font-extrabold text-emerald-600 dark:text-emerald-400 hover:underline">Đăng nhập</a>
        </div>
    </div>
</div>
@endsection
