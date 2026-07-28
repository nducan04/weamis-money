@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto my-6 sm:my-12">
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200/80 dark:border-slate-700">
        <!-- Logo & Header -->
        <div class="text-center mb-6">
            <div class="w-14 h-14 rounded-2xl bg-emerald-500 text-slate-950 font-black text-2xl flex items-center justify-center shadow-lg shadow-emerald-500/20 mx-auto mb-3">
                ✍️
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">Đăng Ký Tài Khoản</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Tham gia quản lý Quỹ Team Weamis</p>
        </div>

        <!-- Register Form -->
        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Họ và tên</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="VD: Nguyễn Văn A" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium focus:ring-2 focus:ring-emerald-500 dark:text-white">
                @error('name')
                    <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Địa chỉ Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="name@weamis.com" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium focus:ring-2 focus:ring-emerald-500 dark:text-white">
                @error('email')
                    <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Mật khẩu</label>
                <input type="password" name="password" required placeholder="Tối thiểu 6 ký tự" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium focus:ring-2 focus:ring-emerald-500 dark:text-white">
                @error('password')
                    <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Xác nhận mật khẩu</label>
                <input type="password" name="password_confirmation" required placeholder="Nhập lại mật khẩu" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium focus:ring-2 focus:ring-emerald-500 dark:text-white">
            </div>

            <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-emerald-600/30 transition mt-2">
                Tạo Tài Khoản
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-slate-500 border-t border-slate-100 dark:border-slate-700/80 pt-4">
            Đã có tài khoản? 
            <a href="{{ route('login') }}" class="font-extrabold text-emerald-600 dark:text-emerald-400 hover:underline">Đăng nhập</a>
        </div>
    </div>
</div>
@endsection
