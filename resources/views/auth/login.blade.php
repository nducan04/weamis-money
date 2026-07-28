@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto my-6 sm:my-12">
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200/80 dark:border-slate-700">
        <!-- Logo & Header -->
        <div class="text-center mb-6">
            <div class="w-14 h-14 rounded-2xl bg-emerald-500 text-slate-950 font-black text-2xl flex items-center justify-center shadow-lg shadow-emerald-500/20 mx-auto mb-3">
                💲
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">Đăng Nhập</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Quản lý thu chi & Phân bổ quỹ Weamis</p>
        </div>

        <!-- Login Form -->
        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@weamis.com" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium focus:ring-2 focus:ring-emerald-500 dark:text-white">
                @error('email')
                    <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Mật khẩu</label>
                    <a href="{{ route('password.request') }}" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline">Quên mật khẩu?</a>
                </div>
                <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium focus:ring-2 focus:ring-emerald-500 dark:text-white">
                @error('password')
                    <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between py-1">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 dark:border-slate-600">
                    <span class="text-xs font-medium text-slate-600 dark:text-slate-300">Ghi nhớ đăng nhập</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-emerald-600/30 transition">
                Đăng Nhập
            </button>
        </form>

        <!-- Quick Login Demo Account Banner -->
        <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-700/80">
            <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2.5 text-center">⚡ Đăng Nhập Nhanh (Tài Khoản Admin Mẫu)</p>
            <div>
                <!-- Việt Admin -->
                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <input type="hidden" name="email" value="viet.nh@weamis.com">
                    <input type="hidden" name="password" value="password">
                    <button type="submit" class="w-full p-3 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 rounded-xl flex items-center justify-between transition">
                        <div class="flex items-center space-x-2.5">
                            <span class="w-7 h-7 rounded-full bg-amber-500 text-slate-950 font-bold text-xs flex items-center justify-center">HV</span>
                            <div class="text-left">
                                <span class="block font-extrabold text-xs text-amber-700 dark:text-amber-300">Nguyễn Hoàng Việt</span>
                                <span class="block text-[10px] text-slate-500">viet.nh@weamis.com</span>
                            </div>
                        </div>
                        <span class="text-[10px] bg-amber-400 text-slate-950 font-extrabold px-2 py-0.5 rounded">Chủ quỹ (Admin)</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-5 text-center text-xs text-slate-500">
            Chưa có tài khoản? 
            <a href="{{ route('register') }}" class="font-extrabold text-emerald-600 dark:text-emerald-400 hover:underline">Đăng ký mới</a>
        </div>
    </div>
</div>
@endsection
