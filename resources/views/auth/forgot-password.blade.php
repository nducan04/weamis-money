<!DOCTYPE html>
<html lang="vi" x-data="{ darkMode: true }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Khôi Phục Mật Khẩu | Weamis Money</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col justify-center items-center p-4 relative overflow-hidden">

    <div class="w-full max-w-md bg-slate-800/90 rounded-3xl p-6 sm:p-8 border border-slate-700 shadow-2xl backdrop-blur-xl relative z-10 space-y-6">

        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-amber-500 text-white font-black text-2xl flex items-center justify-center mx-auto shadow-lg shadow-amber-500/20">
                🔑
            </div>
            <h1 class="text-xl font-black text-white tracking-tight">Khôi Phục Mật Khẩu</h1>
            <p class="text-xs text-slate-400 font-medium">Mật khẩu của tài khoản sẽ được cài lại về mặc định: <strong class="text-amber-400">weamis123</strong></p>
        </div>

        @if($errors->any())
            <div class="p-3 bg-rose-500/10 border border-rose-500/30 rounded-2xl text-xs font-bold text-rose-400 text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1.5">Nhập Email tài khoản cần khôi phục</label>
                <input type="email" name="email" required placeholder="name@weamis.com" class="w-full px-4 py-3 rounded-2xl bg-slate-700/70 border border-slate-600 text-sm font-bold text-white focus:ring-2 focus:ring-amber-500 outline-none transition">
            </div>

            <button type="submit" class="w-full py-3.5 bg-amber-600 hover:bg-amber-500 text-white font-black text-sm rounded-2xl shadow-lg shadow-amber-600/30 transition duration-200 cursor-pointer">
                Xác Nhận Reset Mật Khẩu
            </button>
        </form>

        <div class="text-center pt-2">
            <a href="{{ route('login') }}" class="text-xs font-bold text-slate-400 hover:text-white transition">← Quay lại trang đăng nhập</a>
        </div>

    </div>

</body>
</html>
