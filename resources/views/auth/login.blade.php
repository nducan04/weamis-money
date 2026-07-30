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

    <div class="w-full max-w-md bg-slate-800/90 rounded-3xl p-6 sm:p-8 border border-slate-700 shadow-2xl backdrop-blur-xl relative z-10 space-y-6" x-data="{ selectedEmail: '{{ $users->first()?->email }}' }">

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

        <!-- Quick Select Member Chips -->
        <div>
            <label class="block text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-2">Chọn nhanh tài khoản thành viên:</label>
            <div class="flex flex-wrap gap-1.5 max-h-32 overflow-y-auto pr-1">
                @foreach($users as $u)
                    <button type="button" @click="selectedEmail = '{{ $u->email }}'" class="px-2.5 py-1 rounded-xl text-xs font-extrabold transition flex items-center space-x-1 border" :class="selectedEmail === '{{ $u->email }}' ? 'bg-emerald-600 text-white border-emerald-400 shadow-md' : 'bg-slate-700/60 text-slate-300 border-slate-600 hover:bg-slate-700'">
                        <span>{{ $u->name }}</span>
                        @if($u->isAdmin())
                            <span class="text-[10px]">👑</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Login Form -->
        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1.5">Email tài khoản</label>
                <input type="email" name="email" x-model="selectedEmail" required placeholder="name@weamis.com" class="w-full px-4 py-3 rounded-2xl bg-slate-700/70 border border-slate-600 text-sm font-bold text-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs font-bold text-slate-300">Mật khẩu</label>
                    <a href="{{ route('password.request') }}" class="text-[11px] font-bold text-emerald-400 hover:underline">Quên mật khẩu?</a>
                </div>
                <input type="password" name="password" value="weamis123" required placeholder="••••••••" class="w-full px-4 py-3 rounded-2xl bg-slate-700/70 border border-slate-600 text-sm font-bold text-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
                <p class="text-[10px] text-slate-400 font-medium mt-1">Mật khẩu mặc định khởi tạo: <code class="text-emerald-400 font-bold">weamis123</code></p>
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

        <div class="text-center pt-2 border-t border-slate-700/60 flex items-center justify-between text-xs">
            <span class="text-slate-400 font-medium">Chưa có tài khoản?</span>
            <a href="{{ route('register') }}" class="font-extrabold text-emerald-400 hover:underline">📝 Đăng ký tài khoản mới</a>
        </div>

    </div>

</body>
</html>
