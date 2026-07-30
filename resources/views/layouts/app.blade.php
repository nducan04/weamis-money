<!DOCTYPE html>
<html lang="vi" x-data="{ 
    darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
}" 
x-init="$watch('darkMode', val => { localStorage.setItem('theme', val ? 'dark' : 'light'); if(val) { document.documentElement.classList.add('dark'); } else { document.documentElement.classList.remove('dark'); } })"
:class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#059669">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>{{ config('app.name', 'Weamis Money') }} - Quản Lý Thu Chi Team</title>

    <!-- Favicon Logo for Browser Tab -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>💲</text></svg>">
    
    <!-- Prevent Dark Mode FOUC on reload -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
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

    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        
        /* Smooth Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #334155;
        }

        /* Safe Bottom Padding for Mobile Navigators */
        .safe-bottom {
            padding-bottom: max(1rem, env(safe-area-inset-bottom));
        }

        /* Utility for 2 line truncation */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 min-h-screen flex flex-col transition-colors duration-200">

    <!-- Top Navigation Header (Vibrant Emerald Brand Navbar) -->
    <header class="bg-gradient-to-r from-emerald-500 via-emerald-600 to-teal-700 text-white sticky top-0 z-30 shadow-md border-b border-emerald-800/50 safe-top transition-colors">
        <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-3 sm:px-6 py-2.5 sm:py-3.5 flex items-center justify-between">
            <div class="flex items-center space-x-4 sm:space-x-6 min-w-0">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 sm:space-x-3 min-w-0">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-white text-emerald-700 font-black text-base sm:text-xl flex items-center justify-center shadow-md flex-shrink-0">
                        💲
                    </div>
                    <div class="min-w-0">
                        <h1 class="font-black text-base sm:text-xl tracking-tight leading-none text-white truncate">Weamis Money</h1>
                        <p class="text-[10px] sm:text-xs text-emerald-100 font-semibold hidden xs:block">Quản lý thu chi & Quỹ team</p>
                    </div>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="hidden sm:flex items-center space-x-1 pl-4 border-l border-emerald-400/40">
                    <a href="{{ route('dashboard') }}" 
                       class="px-3.5 py-2 rounded-xl text-sm font-extrabold transition flex items-center space-x-1.5 {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white shadow-inner' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                        <span>📊 Dashboard</span>
                    </a>
                    <a href="{{ route('projects.index') }}" 
                       class="px-3.5 py-2 rounded-xl text-sm font-extrabold transition flex items-center space-x-1.5 {{ request()->routeIs('projects.*') ? 'bg-white/20 text-white shadow-inner' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                        <span>📂 Dự Án</span>
                    </a>
                    <a href="{{ route('analytics.networth') }}" 
                       class="px-3.5 py-2 rounded-xl text-sm font-extrabold transition flex items-center space-x-1.5 {{ request()->routeIs('analytics.*') ? 'bg-white/20 text-white shadow-inner' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                        <span>💎 Tài Sản & Mạng Lưới</span>
                    </a>
                    <a href="{{ route('report') }}" 
                       class="px-3.5 py-2 rounded-xl text-sm font-extrabold transition flex items-center space-x-1.5 {{ request()->routeIs('report') ? 'bg-white/20 text-white shadow-inner' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                        <span>📈 Báo Cáo</span>
                    </a>
                    <a href="{{ route('history') }}" 
                       class="px-3.5 py-2 rounded-xl text-sm font-extrabold transition flex items-center space-x-1.5 {{ request()->routeIs('history') ? 'bg-white/20 text-white shadow-inner' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                        <span>📜 Lịch Sử GD</span>
                    </a>
                </nav>
            </div>
            
            <div class="flex items-center space-x-2 sm:space-x-3 flex-shrink-0">
                <!-- Dark Mode Toggle -->
                <button @click="darkMode = !darkMode" class="p-2 sm:p-2.5 rounded-xl bg-emerald-800/60 hover:bg-emerald-800 border border-emerald-500/40 text-xs font-bold text-white flex items-center space-x-1.5 transition cursor-pointer">
                    <span x-show="!darkMode">🌙</span>
                    <span x-show="darkMode">☀️</span>
                    <span class="hidden md:inline" x-show="!darkMode">Tối</span>
                    <span class="hidden md:inline" x-show="darkMode">Sáng</span>
                </button>

                <!-- User Profile & Dropdown -->
                @auth
                <div class="relative" x-data="{ openProfile: false }">
                    <button @click="openProfile = !openProfile" class="flex items-center space-x-2 p-1.5 sm:px-3 sm:py-1.5 bg-white/10 hover:bg-white/20 rounded-xl border border-white/20 text-white transition cursor-pointer">
                        <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-emerald-900 text-white font-black text-xs flex items-center justify-center overflow-hidden border border-white/40">
                            @if(auth()->user()->avatar && \Illuminate\Support\Str::startsWith(auth()->user()->avatar, ['http://', 'https://', '/uploads/']))
                                <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                            @else
                                {{ auth()->user()->avatar ?? substr(auth()->user()->name, 0, 2) }}
                            @endif
                        </div>
                        <div class="text-left hidden sm:block">
                            <p class="text-xs font-black leading-none flex items-center space-x-1">
                                <span>{{ auth()->user()->name }}</span>
                                @if(auth()->user()->isAdmin())
                                    <span class="px-1.5 py-0.5 bg-amber-400 text-slate-900 rounded font-black text-[9px] uppercase">Admin</span>
                                @elseif(auth()->user()->isLead())
                                    <span class="px-1.5 py-0.5 bg-indigo-400 text-white rounded font-black text-[9px] uppercase">Lead</span>
                                @endif
                            </p>
                        </div>
                        <svg class="w-3.5 h-3.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <!-- Dropdown menu -->
                    <div x-show="openProfile" @click.away="openProfile = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl py-2 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 z-50 text-xs font-bold">
                        <div class="px-3 py-2 border-b border-slate-100 dark:border-slate-700 sm:hidden">
                            <p class="font-extrabold text-sm text-slate-900 dark:text-white">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-slate-400 font-semibold">{{ auth()->user()->email }}</p>
                        </div>
                        @if(auth()->user()?->isAdmin())
                            <a href="{{ route('members.index') }}" class="px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center space-x-2 transition text-emerald-600 dark:text-emerald-400 font-bold">
                                <span>👥 Quản lý người dùng</span>
                            </a>
                        @endif
                        <a href="{{ route('password.change') }}" class="px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center space-x-2 transition">
                            <span>🔐 Đổi mật khẩu</span>
                        </a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 flex items-center space-x-2 transition cursor-pointer">
                                <span>🚪 Đăng xuất</span>
                            </button>
                        </form>
                    </div>
                </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-3 sm:px-6 py-5 sm:py-8 flex-grow w-full min-h-[75vh]">
        <!-- Auto-Dismissing Floating Toast Notifications (4 seconds) -->
        @if(session('success'))
            <div x-data="{ show: true }" 
                 x-init="setTimeout(() => show = false, 4000)" 
                 x-show="show" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-3 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 -translate-y-3 scale-95"
                 x-cloak
                 class="fixed top-5 right-5 z-50 max-w-md p-4 rounded-2xl bg-emerald-600 text-white shadow-2xl flex items-center space-x-3 border border-emerald-400/40 backdrop-blur-md">
                <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center font-bold text-lg flex-shrink-0">
                    ✓
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-extrabold text-[10px] uppercase tracking-wider text-emerald-100">Thông báo</p>
                    <p class="font-bold text-xs sm:text-sm leading-snug">{{ session('success') }}</p>
                </div>
                <button @click="show = false" class="text-white/80 hover:text-white text-base font-bold px-1.5 cursor-pointer">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" 
                 x-init="setTimeout(() => show = false, 4000)" 
                 x-show="show" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-3 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 -translate-y-3 scale-95"
                 x-cloak
                 class="fixed top-5 right-5 z-50 max-w-md p-4 rounded-2xl bg-rose-600 text-white shadow-2xl flex items-center space-x-3 border border-rose-400/40 backdrop-blur-md">
                <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center font-bold text-lg flex-shrink-0">
                    ✕
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-extrabold text-[10px] uppercase tracking-wider text-rose-100">Lỗi</p>
                    <p class="font-bold text-xs sm:text-sm leading-snug">{{ session('error') }}</p>
                </div>
                <button @click="show = false" class="text-white/80 hover:text-white text-base font-bold px-1.5 cursor-pointer">✕</button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Compact Streamlined Footer matching Navbar Gradient -->
    <footer class="bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-800 text-white border-t border-emerald-500/30 transition-colors shadow-inner flex-shrink-0 mt-auto">
        <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="flex items-center space-x-3">
                <div class="w-7 h-7 rounded-lg bg-white text-emerald-700 font-black text-sm flex items-center justify-center shadow">
                    💲
                </div>
                <span class="font-extrabold text-sm text-white tracking-tight">Weamis Money</span>
                <span class="text-emerald-200/80 font-medium hidden md:inline">| Hệ thống quản lý tài chính, phân bổ dự án & theo dõi quỹ team minh bạch</span>
            </div>
            <div class="flex items-center space-x-4 text-emerald-100/90 font-semibold">
                <a href="{{ route('dashboard') }}" class="hover:text-white transition">Dashboard</a>
                <a href="{{ route('projects.index') }}" class="hover:text-white transition">Dự Án</a>
                <a href="{{ route('analytics.networth') }}" class="hover:text-white transition">Net Worth</a>
                <a href="{{ route('report') }}" class="hover:text-white transition">Báo Cáo</a>
                <a href="{{ route('history') }}" class="hover:text-white transition">Lịch Sử</a>
                <span class="text-emerald-300/60 font-normal">© {{ date('Y') }} Team Weamis</span>
            </div>
        </div>
    </footer>
</body>
</html>
