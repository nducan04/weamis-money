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
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
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
            background-color: #f8fafc;
        }
        .dark body {
            background-color: #0f172a;
        }
    </style>
</head>
<body class="text-slate-800 dark:text-slate-100 min-h-screen transition-colors duration-200">

    <!-- Top Navigation Header -->
    <header class="bg-slate-900 text-white sticky top-0 z-30 shadow-md border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500 text-slate-950 font-black text-xl flex items-center justify-center shadow-lg shadow-emerald-500/20">
                        💲
                    </div>
                    <div>
                        <h1 class="font-extrabold text-lg tracking-tight leading-none text-white">weamis-money</h1>
                        <p class="text-xs text-slate-400 font-medium">Hệ thống Quản lý Thu Chi & Phân bổ Quỹ Team</p>
                    </div>
                </a>
            </div>
            
            <div class="flex items-center space-x-3">
                <span class="hidden sm:inline-flex items-center px-3 py-1 bg-slate-800 border border-slate-700 text-slate-300 text-xs font-semibold rounded-full">
                    🏢 Quỹ: Trả nợ thuê Ltd
                </span>

                <!-- Dark Mode Toggle -->
                <button @click="darkMode = !darkMode" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-slate-200 flex items-center space-x-1 transition">
                    <span x-show="!darkMode">🌙 Tối</span>
                    <span x-show="darkMode">☀️ Sáng</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
        <!-- Flash Notifications -->
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" class="mb-5 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 flex items-center justify-between backdrop-blur">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-bold text-sm">{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-xs font-bold opacity-70 hover:opacity-100">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" class="mb-5 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 flex items-center justify-between backdrop-blur">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-bold text-sm">{{ session('error') }}</span>
                </div>
                <button @click="show = false" class="text-xs font-bold opacity-70 hover:opacity-100">✕</button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="py-6 text-center text-xs text-slate-400 dark:text-slate-500 border-t border-slate-200 dark:border-slate-800 mt-12">
        <p>© 2026 Weamis Money Dashboard - Quản lý Thu Chi & Phân bổ Lợi nhuận Team</p>
    </footer>
</body>
</html>
