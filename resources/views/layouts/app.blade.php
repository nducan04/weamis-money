<!DOCTYPE html>
<html lang="vi" x-data="{ darkMode: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Weamis Money') }} - Quản Lý Thu Chi Team</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        momo: {
                            50: '#fdf2f8',
                            100: '#fce7f3',
                            200: '#fbcfe8',
                            500: '#d82d8b',
                            600: '#c2185b',
                            700: '#9e1147',
                            800: '#83123f',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f4f5f8;
        }
        .dark body {
            background-color: #0f172a;
        }
        .momo-gradient {
            background: linear-gradient(135deg, #d82d8b 0%, #ad1457 100%);
        }
        .momo-card-shadow {
            box-shadow: 0 10px 30px -5px rgba(216, 45, 139, 0.15);
        }
    </style>
</head>
<body class="text-slate-800 dark:text-slate-100 min-h-screen transition-colors duration-200">

    <!-- Top Navigation Header -->
    <header class="momo-gradient text-white sticky top-0 z-30 shadow-md">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                    <div class="w-9 h-9 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center font-black text-xl text-white shadow-inner">
                        M
                    </div>
                    <div>
                        <h1 class="font-extrabold text-lg tracking-tight leading-none">weamis-money</h1>
                        <p class="text-[11px] text-pink-100/80 font-medium">Quản lý Quỹ & Phân chia % Team</p>
                    </div>
                </a>
            </div>
            
            <div class="flex items-center space-x-2">
                <!-- Dark Mode Toggle -->
                <button @click="darkMode = !darkMode" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 transition-all text-sm font-medium flex items-center space-x-1.5">
                    <span x-show="!darkMode">🌙 Tối</span>
                    <span x-show="darkMode">☀️ Sáng</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-5xl mx-auto px-4 py-6">
        <!-- Flash Notifications -->
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" class="mb-4 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 flex items-center justify-between backdrop-blur">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-semibold text-sm">{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-xs opacity-70 hover:opacity-100">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" class="mb-4 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 flex items-center justify-between backdrop-blur">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-semibold text-sm">{{ session('error') }}</span>
                </div>
                <button @click="show = false" class="text-xs opacity-70 hover:opacity-100">✕</button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="py-6 text-center text-xs text-slate-400 dark:text-slate-500 border-t border-slate-200/50 dark:border-slate-800/50 mt-10">
        <p>© 2026 Weamis Money - Phát triển dựa trên quy trình Quỹ Nhóm MoMo</p>
    </footer>
</body>
</html>
