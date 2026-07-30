@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto space-y-6">

    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700 shadow-md">
        <div class="text-center mb-6">
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-black text-xl flex items-center justify-center mx-auto mb-2">
                🔐
            </div>
            <h2 class="text-xl font-black text-slate-900 dark:text-white">Đổi Mật Khẩu Cá Nhân</h2>
            <p class="text-xs text-slate-400 font-medium mt-1">Đổi mật khẩu cho tài khoản: <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})</p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 bg-rose-500/10 border border-rose-500/30 rounded-2xl text-xs font-bold text-rose-600 dark:text-rose-400 text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Mật khẩu hiện tại</label>
                <input type="password" name="current_password" required placeholder="••••••••" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs sm:text-sm font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Mật khẩu mới</label>
                <input type="password" name="new_password" required minlength="6" placeholder="Tối thiểu 6 ký tự" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs sm:text-sm font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Xác nhận mật khẩu mới</label>
                <input type="password" name="new_password_confirmation" required minlength="6" placeholder="Nhập lại mật khẩu mới" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs sm:text-sm font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <div class="pt-2 flex justify-between items-center">
                <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl">Quay lại</a>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-md cursor-pointer">Lưu Mật Khẩu Mới</button>
            </div>
        </form>
    </div>

</div>
@endsection
