<!DOCTYPE html>
<html lang="vi" x-data="{ darkMode: true }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng Ký Tài Khoản | Weamis Money</title>
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
                📝
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Tạo Tài Khoản Mới</h1>
            <p class="text-xs text-slate-400 font-medium">Tham gia quản lý tài chính & quỹ nhóm Weamis</p>
        </div>

        @if($errors->any())
            <div class="p-3 bg-rose-500/10 border border-rose-500/30 rounded-2xl text-xs font-bold text-rose-400 text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Registration Form -->
        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Họ và Tên</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="VD: Nguyễn Văn A" class="w-full px-4 py-3 rounded-2xl bg-slate-700/70 border border-slate-600 text-sm font-bold text-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Tên đăng nhập (Username)</label>
                    <input type="text" name="username" value="{{ old('username') }}" required placeholder="nva" class="w-full px-4 py-3 rounded-2xl bg-slate-700/70 border border-slate-600 text-sm font-bold text-white focus:ring-2 focus:ring-emerald-500 outline-none transition lowercase">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1.5">Email cá nhân</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="name@example.com" class="w-full px-4 py-3 rounded-2xl bg-slate-700/70 border border-slate-600 text-sm font-bold text-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1.5">Mật khẩu</label>
                <input type="password" name="password" required minlength="6" placeholder="Tối thiểu 6 ký tự" class="w-full px-4 py-3 rounded-2xl bg-slate-700/70 border border-slate-600 text-sm font-bold text-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1.5">Xác nhận mật khẩu</label>
                <input type="password" name="password_confirmation" required minlength="6" placeholder="Nhập lại mật khẩu" class="w-full px-4 py-3 rounded-2xl bg-slate-700/70 border border-slate-600 text-sm font-bold text-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
            </div>

            <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-sm rounded-2xl shadow-lg shadow-emerald-600/30 transition-all duration-200 cursor-pointer flex items-center justify-center space-x-2">
                <span>Tạo Tài Khoản & Vào Dashboard ➔</span>
            </button>
        </form>

        <div class="text-center pt-2 border-t border-slate-700/60 flex items-center justify-between text-xs">
            <span class="text-slate-400 font-medium">Đã có tài khoản?</span>
            <a href="{{ route('login') }}" class="font-extrabold text-emerald-400 hover:underline">🔑 Đăng nhập ngay</a>
        </div>

    </div>

</body>
</html>
