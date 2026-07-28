@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto my-6 sm:my-12">
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200/80 dark:border-slate-700">
        <!-- Logo & Header -->
        <div class="text-center mb-6">
            <div class="w-14 h-14 rounded-2xl bg-amber-500 text-slate-950 font-black text-2xl flex items-center justify-center shadow-lg shadow-amber-500/20 mx-auto mb-3">
                🔐
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">Quên Mật Khẩu</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Nhập email để nhận link hướng dẫn đặt lại mật khẩu</p>
        </div>

        <!-- Forgot Password Form -->
        <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Địa chỉ Email đăng ký</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@weamis.com" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium focus:ring-2 focus:ring-emerald-500 dark:text-white">
                @error('email')
                    <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full py-3.5 bg-amber-500 hover:bg-amber-600 active:scale-95 text-slate-950 font-extrabold text-sm rounded-xl shadow-lg shadow-amber-500/30 transition">
                Gửi Link Khôi Phục
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-slate-500 border-t border-slate-100 dark:border-slate-700/80 pt-4">
            Quay lại trang 
            <a href="{{ route('login') }}" class="font-extrabold text-emerald-600 dark:text-emerald-400 hover:underline">Đăng nhập</a>
        </div>
    </div>
</div>
@endsection
