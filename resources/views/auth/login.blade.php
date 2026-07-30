<!DOCTYPE html>
<html lang="vi" x-data="{ darkMode: true }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng Nhập | Weamis Money</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col justify-center items-center p-4 relative overflow-hidden">

    <!-- Background glowing ambient lights -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-teal-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md bg-slate-800/90 rounded-3xl p-6 sm:p-8 border border-slate-700 shadow-2xl backdrop-blur-xl relative z-10 space-y-6">

        <!-- Logo & Header -->
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white font-black text-2xl flex items-center justify-center mx-auto shadow-lg shadow-emerald-500/20">
                💲
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Weamis Money</h1>
            <p class="text-xs text-slate-400 font-medium">Hệ thống quản lý tài chính & quỹ nhóm nội bộ Weamis</p>
        </div>

        <!-- Session Flash Messages -->
        @if(session('success'))
            <div class="p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl text-xs font-bold text-emerald-400 text-center">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-3 bg-rose-500/10 border border-rose-500/30 rounded-2xl text-xs font-bold text-rose-400 text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Minimal Clean Login Form -->
        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1.5">Tên đăng nhập</label>
                <input type="text" name="login" value="{{ old('login') }}" required placeholder="VD: nhv, hts hoặc admin" class="w-full px-4 py-3 rounded-2xl bg-slate-700/70 border border-slate-600 text-sm font-bold text-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs font-bold text-slate-300">Mật khẩu</label>
                </div>
                <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-3 rounded-2xl bg-slate-700/70 border border-slate-600 text-sm font-bold text-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center space-x-2 text-xs text-slate-300 font-semibold cursor-pointer">
                    <input type="checkbox" name="remember" checked class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 bg-slate-700 border-slate-600">
                    <span>Ghi nhớ đăng nhập</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-sm rounded-2xl shadow-lg shadow-emerald-600/30 transition-all duration-200 cursor-pointer flex items-center justify-center space-x-2">
                <span>Đăng Nhập ➔</span>
            </button>
        </form>

        <div class="text-center pt-2 border-t border-slate-700/60">
            <p class="text-[11px] text-slate-400 font-medium">© {{ date('Y') }} Team Weamis. Hệ thống quản lý tài chính nội bộ.</p>
        </div>

    </div>

</body>
</html>
