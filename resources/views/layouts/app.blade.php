<!DOCTYPE html>
<html lang="vi" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches), mobileMenuOpen: false }"
      :class="{ 'dark': darkMode }"
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Weamis Money') }}</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (via CDN) -->
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
        [x-cloak] { display: none !important; }

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
            padding-bottom: max(0.75rem, env(safe-area-inset-bottom));
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
            <div class="flex items-center space-x-3 sm:space-x-6 min-w-0">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 sm:space-x-3 min-w-0">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-white text-emerald-700 font-black text-base sm:text-xl flex items-center justify-center shadow-md flex-shrink-0">
                        💲
                    </div>
                    <div class="min-w-0">
                        <h1 class="font-black text-base sm:text-xl tracking-tight leading-none text-white truncate">Weamis Money</h1>
                        <p class="text-[10px] sm:text-xs text-emerald-100 font-semibold hidden xs:block">Quản lý thu chi & Quỹ team</p>
                    </div>
                </a>

                <!-- Desktop Navigation Links (Consolidated 3 Main Pages) -->
                <nav class="hidden sm:flex items-center space-x-1 pl-4 border-l border-emerald-400/40">
                    <a href="{{ route('dashboard') }}" 
                       class="px-3.5 py-2 rounded-xl text-sm font-extrabold transition flex items-center space-x-1.5 {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white shadow-inner' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                        <span>Tổng Quan</span>
                    </a>
                    <a href="{{ route('history') }}" 
                       class="px-3.5 py-2 rounded-xl text-sm font-extrabold transition flex items-center space-x-1.5 {{ request()->routeIs('history') ? 'bg-white/20 text-white shadow-inner' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                        <span>Giao Dịch</span>
                    </a>
                    <a href="{{ route('projects.index') }}" 
                       class="px-3.5 py-2 rounded-xl text-sm font-extrabold transition flex items-center space-x-1.5 {{ request()->routeIs('projects.*') ? 'bg-white/20 text-white shadow-inner' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                        <span>Dự Án</span>
                    </a>
                    <a href="{{ route('analytics.networth') }}" 
                       class="px-3.5 py-2 rounded-xl text-sm font-extrabold transition flex items-center space-x-1.5 {{ request()->routeIs('analytics.*') ? 'bg-white/20 text-white shadow-inner' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                        <span>Tài Sản & Thành Viên</span>
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

                <!-- User Profile & Dropdown (Auth) OR Guest Login Button (Guest) -->
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
                                @if(auth()->user()->isAdmin())
                                    <span class="px-1.5 py-0.5 bg-amber-400 text-slate-900 rounded font-black text-[9px] uppercase">Admin</span>
                                @elseif(auth()->user()->isLead())
                                    <span class="px-1.5 py-0.5 bg-indigo-400 text-white rounded font-black text-[9px] uppercase">Lead</span>
                                @else
                                    <span class="text-xs text-white font-bold">{{ auth()->user()->name }}</span>
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
                @else
                <div class="flex items-center space-x-2">
                    <span class="hidden md:inline-flex items-center px-2.5 py-1 rounded-xl bg-white/10 border border-white/20 text-[11px] font-bold text-emerald-100">
                        Chế độ khách
                    </span>
                    <a href="{{ route('login') }}" class="px-3 sm:px-4 py-1.5 sm:py-2 bg-white text-emerald-800 hover:bg-emerald-50 rounded-xl font-black text-xs sm:text-sm shadow-md transition-all duration-200 flex items-center space-x-1.5 cursor-pointer">
                        <span>🔑 Đăng nhập</span>
                    </a>
                </div>
                @endauth

                <!-- Mobile Hamburger Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="sm:hidden p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-lg transition flex items-center justify-center">
                    <span x-show="!mobileMenuOpen">☰</span>
                    <span x-show="mobileMenuOpen">✕</span>
                </button>
            </div>
        </div>

        <!-- Mobile Slide-over Drawer -->
        <div x-show="mobileMenuOpen" x-cloak class="sm:hidden border-t border-emerald-400/30 bg-emerald-800/95 backdrop-blur-md px-4 py-3 space-y-2 text-xs font-bold text-white shadow-xl" x-transition>
            @auth
            <div class="pb-2 border-b border-emerald-700/60 flex items-center justify-between">
                <div>
                    <p class="font-extrabold text-sm text-white">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-emerald-200">{{ auth()->user()->email }}</p>
                </div>
                @if(auth()->user()->isAdmin())
                    <span class="px-2 py-0.5 bg-amber-400 text-slate-950 rounded-lg text-[10px] font-black uppercase">Admin</span>
                @endif
            </div>
            @if(auth()->user()?->isAdmin())
                <a href="{{ route('members.index') }}" @click="mobileMenuOpen = false" class="block py-2 px-3 rounded-xl hover:bg-emerald-700/60 transition flex items-center space-x-2">
                    <span>👥 Quản lý người dùng</span>
                </a>
            @endif
            <a href="{{ route('password.change') }}" @click="mobileMenuOpen = false" class="block py-2 px-3 rounded-xl hover:bg-emerald-700/60 transition flex items-center space-x-2">
                <span>🔐 Đổi mật khẩu</span>
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full text-left py-2 px-3 rounded-xl hover:bg-rose-600/40 text-rose-200 flex items-center space-x-2 transition">
                    <span>🚪 Đăng xuất</span>
                </button>
            </form>
            @else
            <div class="pb-2 border-b border-emerald-700/60">
                <p class="font-extrabold text-sm text-white">Chế độ khách</p>
                <p class="text-[10px] text-emerald-200 font-medium">Bạn đang xem dữ liệu ở chế độ chỉ đọc.</p>
            </div>
            <a href="{{ route('login') }}" @click="mobileMenuOpen = false" class="block text-center py-2.5 px-4 rounded-xl bg-white text-emerald-900 font-extrabold text-xs shadow-md transition">
                🔑 Đăng Nhập Thành Viên
            </a>
            @endauth
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-3 sm:px-6 py-5 sm:py-8 flex-grow w-full pb-6 sm:pb-8">
        <!-- Auto-Dismissing Floating Toast Notifications (4 seconds) -->
        @if(session('success'))
            <div x-data="{ show: true }" 
                 x-init="setTimeout(() => show = false, 4000)" 
                 x-show="show" 
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2"
                 class="fixed top-20 right-4 z-50 bg-emerald-600 text-white px-5 py-3 rounded-2xl shadow-xl border border-emerald-400/30 flex items-center space-x-3 text-xs sm:text-sm font-extrabold max-w-sm">
                <span class="text-base">✅</span>
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="ml-auto text-emerald-200 hover:text-white font-bold text-sm">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" 
                 x-init="setTimeout(() => show = false, 5000)" 
                 x-show="show" 
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2"
                 class="fixed top-20 right-4 z-50 bg-rose-600 text-white px-5 py-3 rounded-2xl shadow-xl border border-rose-400/30 flex items-center space-x-3 text-xs sm:text-sm font-extrabold max-w-sm">
                <span class="text-base">⚠️</span>
                <span>{{ session('error') }}</span>
                <button @click="show = false" class="ml-auto text-rose-200 hover:text-white font-bold text-sm">✕</button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bottom Mobile Navigation Bar (Fixed at bottom for easy thumb access) -->
    <div class="sm:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-lg border-t border-slate-200/80 dark:border-slate-800 px-2 py-1.5 shadow-2xl safe-bottom">
        <div class="grid grid-cols-4 gap-1 max-w-md mx-auto">
            
            <!-- Tab 1: Tổng Quan -->
            <a href="{{ route('dashboard') }}" 
               class="flex flex-col items-center justify-center py-1.5 px-0.5 rounded-xl transition cursor-pointer {{ request()->routeIs('dashboard') ? 'text-emerald-600 dark:text-emerald-400 font-black scale-105 bg-emerald-50/50 dark:bg-emerald-950/30' : 'text-slate-400 dark:text-slate-400 font-semibold hover:text-slate-700 dark:hover:text-slate-200' }}">
                <div class="w-5 h-5 mb-0.5 bg-current" style="-webkit-mask: url('/icons/Dashboard.svg') center/contain no-repeat; mask: url('/icons/Dashboard.svg') center/contain no-repeat;"></div>
                <span class="text-[10px] font-extrabold tracking-tight">Tổng Quan</span>
            </a>

            <!-- Tab 2: Giao Dịch -->
            <a href="{{ route('history') }}" 
               class="flex flex-col items-center justify-center py-1.5 px-0.5 rounded-xl transition cursor-pointer {{ request()->routeIs('history') ? 'text-emerald-600 dark:text-emerald-400 font-black scale-105 bg-emerald-50/50 dark:bg-emerald-950/30' : 'text-slate-400 dark:text-slate-400 font-semibold hover:text-slate-700 dark:hover:text-slate-200' }}">
                <div class="w-5 h-5 mb-0.5 bg-current text-lg flex items-center justify-center">📝</div>
                <span class="text-[10px] font-extrabold tracking-tight">Giao Dịch</span>
            </a>

            <!-- Tab 3: Dự Án -->
            <a href="{{ route('projects.index') }}" 
               class="flex flex-col items-center justify-center py-1.5 px-0.5 rounded-xl transition cursor-pointer {{ request()->routeIs('projects.*') ? 'text-emerald-600 dark:text-emerald-400 font-black scale-105 bg-emerald-50/50 dark:bg-emerald-950/30' : 'text-slate-400 dark:text-slate-400 font-semibold hover:text-slate-700 dark:hover:text-slate-200' }}">
                <div class="w-5 h-5 mb-0.5 bg-current" style="-webkit-mask: url('/icons/Project.svg') center/contain no-repeat; mask: url('/icons/Project.svg') center/contain no-repeat;"></div>
                <span class="text-[10px] font-extrabold tracking-tight">Dự Án</span>
            </a>

            <!-- Tab 4: Tài Sản & Thành Viên -->
            <a href="{{ route('analytics.networth') }}" 
               class="flex flex-col items-center justify-center py-1.5 px-0.5 rounded-xl transition cursor-pointer {{ request()->routeIs('analytics.*') ? 'text-emerald-600 dark:text-emerald-400 font-black scale-105 bg-emerald-50/50 dark:bg-emerald-950/30' : 'text-slate-400 dark:text-slate-400 font-semibold hover:text-slate-700 dark:hover:text-slate-200' }}">
                <div class="w-5 h-5 mb-0.5 bg-current" style="-webkit-mask: url('/icons/Money.svg') center/contain no-repeat; mask: url('/icons/Money.svg') center/contain no-repeat;"></div>
                <span class="text-[10px] font-extrabold tracking-tight">Tài Sản</span>
            </a>

        </div>
    </div>

    <!-- Compact Streamlined Footer matching Navbar Gradient (hidden on mobile) -->
    <footer class="hidden sm:block bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-800 text-white border-t border-emerald-500/30 transition-colors shadow-inner flex-shrink-0 mt-auto">
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
                <a href="{{ route('history') }}" class="hover:text-white transition">Giao Dịch</a>
                <span class="text-emerald-300/60 font-normal">© {{ date('Y') }} Team Weamis</span>
            </div>
        </div>
    </footer>
</body>
</html>
