<!DOCTYPE html>
<html lang="vi" x-data="{ 
    darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches), 
    mobileMenuOpen: false,
    searchQuery: '',
    omniboxFocused: false,
    showHelpModal: false
}"
      :class="{ 'dark': darkMode }"
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Weamis Money') }}</title>

    <!-- Google Fonts: Roboto & Google Sans Style Display Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Roboto+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (via CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Google Sans"', '"Plus Jakarta Sans"', 'Roboto', 'sans-serif'],
                        mono: ['"Roboto Mono"', 'monospace'],
                    },
                    colors: {
                        // Google Material 3 / Google Finance Palette
                        google: {
                            blue: '#1a73e8',
                            blueHover: '#1557b0',
                            blueSurface: '#e8f0fe',
                            green: '#137333',
                            greenSurface: '#e6f4ea',
                            greenDark: '#81c995',
                            red: '#c5221f',
                            redSurface: '#fce8e6',
                            redDark: '#f28b82',
                            yellow: '#f29900',
                            yellowSurface: '#fef7e0',
                            surface: '#f8f9fa',
                            surfaceDark: '#131314',
                            cardDark: '#1e1f20',
                            borderDark: '#282a2c',
                            containerDark: '#28292a'
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
            font-family: 'Google Sans', 'Plus Jakarta Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        
        /* Google Smooth Minimal Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #dadce0;
            border-radius: 9999px;
            border: 2px solid transparent;
            background-clip: content-box;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #3c4043;
            border: 2px solid transparent;
            background-clip: content-box;
        }

        .safe-bottom {
            padding-bottom: max(0.75rem, env(safe-area-inset-bottom));
        }

        .line-clamp-1 {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-[#f8f9fa] dark:bg-[#131314] text-[#202124] dark:text-[#e3e3e3] min-h-screen flex flex-col transition-colors duration-200">

    <!-- Top Navigation Header (Google Workspace / Material 3 Style) -->
    <header class="sticky top-0 z-40 bg-white/90 dark:bg-[#1e1f20]/90 backdrop-blur-md border-b border-[#dadce0] dark:border-[#282a2c] transition-colors">
        <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 py-2.5 flex items-center justify-between gap-4">
            
            <!-- Left: Logo & Navigation Tabs -->
            <div class="flex items-center space-x-6 min-w-0">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 flex-shrink-0 group">
                    <div class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-[#1a73e8] to-[#4285f4] text-white flex items-center justify-center font-bold text-lg shadow-sm group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="min-w-0">
                        <h1 class="font-bold text-base sm:text-lg tracking-tight text-[#202124] dark:text-[#e3e3e3] leading-none flex items-center gap-1.5">
                            <span>Weamis Money</span>
                        </h1>
                    </div>
                </a>

                <!-- Google Material Navigation Tabs -->
                <nav class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('dashboard') }}" 
                       class="px-4 py-2 rounded-full text-xs font-bold transition-all flex items-center space-x-1.5 {{ request()->routeIs('dashboard') ? 'bg-[#c2e7ff] text-[#001d35] dark:bg-[#004a77] dark:text-[#c2e7ff]' : 'text-[#5f6368] dark:text-[#9aa0a6] hover:bg-[#f1f3f4] dark:hover:bg-[#282a2c] hover:text-[#202124] dark:hover:text-white' }}">
                        <span>Tổng Quan</span>
                    </a>
                    <a href="{{ route('history') }}" 
                       class="px-4 py-2 rounded-full text-xs font-bold transition-all flex items-center space-x-1.5 {{ request()->routeIs('history') ? 'bg-[#c2e7ff] text-[#001d35] dark:bg-[#004a77] dark:text-[#c2e7ff]' : 'text-[#5f6368] dark:text-[#9aa0a6] hover:bg-[#f1f3f4] dark:hover:bg-[#282a2c] hover:text-[#202124] dark:hover:text-white' }}">
                        <span>Giao Dịch</span>
                    </a>
                    <a href="{{ route('projects.index') }}" 
                       class="px-4 py-2 rounded-full text-xs font-bold transition-all flex items-center space-x-1.5 {{ request()->routeIs('projects.*') ? 'bg-[#c2e7ff] text-[#001d35] dark:bg-[#004a77] dark:text-[#c2e7ff]' : 'text-[#5f6368] dark:text-[#9aa0a6] hover:bg-[#f1f3f4] dark:hover:bg-[#282a2c] hover:text-[#202124] dark:hover:text-white' }}">
                        <span>Dự Án</span>
                    </a>
                    <a href="{{ route('analytics.networth') }}" 
                       class="px-4 py-2 rounded-full text-xs font-bold transition-all flex items-center space-x-1.5 {{ request()->routeIs('analytics.*') ? 'bg-[#c2e7ff] text-[#001d35] dark:bg-[#004a77] dark:text-[#c2e7ff]' : 'text-[#5f6368] dark:text-[#9aa0a6] hover:bg-[#f1f3f4] dark:hover:bg-[#282a2c] hover:text-[#202124] dark:hover:text-white' }}">
                        <span>Tài Sản & Net Worth</span>
                    </a>
                </nav>
            </div>

            <!-- Center/Right: Google Pill Omnibox & User Controls -->
            <div class="flex items-center space-x-3 flex-shrink-0">
                
                <!-- Quick Link to History Search (Pill Search Bar) -->
                <a href="{{ route('history') }}" class="hidden lg:flex items-center space-x-2.5 px-4 py-2 bg-[#f1f3f4] dark:bg-[#282a2c] hover:bg-[#e8eaed] dark:hover:bg-[#303134] text-[#5f6368] dark:text-[#9aa0a6] rounded-full text-xs font-medium border border-transparent hover:border-[#dadce0] dark:hover:border-[#3c4043] transition-all w-52 xl:w-64">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <span class="truncate">Tìm giao dịch, quỹ...</span>
                    <span class="ml-auto text-[10px] bg-white dark:bg-[#1e1f20] px-1.5 py-0.5 rounded border border-[#dadce0] dark:border-[#3c4043] font-mono">/</span>
                </a>

                <!-- Dark Mode Toggle Button -->
                <button @click="darkMode = !darkMode" 
                        class="w-9 h-9 rounded-full flex items-center justify-center text-[#5f6368] dark:text-[#9aa0a6] hover:bg-[#f1f3f4] dark:hover:bg-[#282a2c] transition-colors cursor-pointer"
                        :title="darkMode ? 'Chuyển sang giao diện Sáng' : 'Chuyển sang giao diện Tối'">
                    <span x-show="!darkMode">🌙</span>
                    <span x-show="darkMode">☀️</span>
                </button>

                <!-- Profile Dropdown (Material You Avatar) -->
                @auth
                <div class="relative" x-data="{ openProfile: false }">
                    <button @click="openProfile = !openProfile" class="flex items-center space-x-2 p-1 rounded-full hover:bg-[#f1f3f4] dark:hover:bg-[#282a2c] transition-colors cursor-pointer">
                        <div class="w-8 h-8 rounded-full bg-[#1a73e8] text-white font-bold text-xs flex items-center justify-center overflow-hidden ring-2 ring-transparent hover:ring-[#c2e7ff] transition-all">
                            @if(auth()->user()->avatar && \Illuminate\Support\Str::startsWith(auth()->user()->avatar, ['http://', 'https://', '/uploads/']))
                                <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                            @else
                                {{ auth()->user()->avatar ?? substr(auth()->user()->name, 0, 2) }}
                            @endif
                        </div>
                    </button>

                    <!-- Google Style Profile Card Dropdown -->
                    <div x-show="openProfile" @click.away="openProfile = false" x-cloak 
                         class="absolute right-0 mt-2 w-72 bg-white dark:bg-[#1e1f20] rounded-3xl shadow-xl py-3 border border-[#dadce0] dark:border-[#282a2c] text-[#202124] dark:text-[#e3e3e3] z-50 text-xs">
                        <div class="px-4 py-3 border-b border-[#f1f3f4] dark:border-[#282a2c] text-center">
                            <div class="w-12 h-12 rounded-full bg-[#1a73e8] text-white font-bold text-base flex items-center justify-center mx-auto mb-2 overflow-hidden shadow-sm">
                                @if(auth()->user()->avatar && \Illuminate\Support\Str::startsWith(auth()->user()->avatar, ['http://', 'https://', '/uploads/']))
                                    <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                @else
                                    {{ auth()->user()->avatar ?? substr(auth()->user()->name, 0, 2) }}
                                @endif
                            </div>
                            <p class="font-bold text-sm text-[#202124] dark:text-white truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] text-[#5f6368] dark:text-[#9aa0a6] truncate">{{ auth()->user()->email }}</p>
                            <div class="mt-2">
                                @if(auth()->user()->isAdmin())
                                    <span class="px-2.5 py-0.5 bg-[#fef7e0] text-[#b06000] dark:bg-[#3c2a00] dark:text-[#fdd663] rounded-full text-[10px] font-bold uppercase tracking-wider">Admin</span>
                                @elseif(auth()->user()->isLead())
                                    <span class="px-2.5 py-0.5 bg-[#e8f0fe] text-[#1a73e8] dark:bg-[#002b4d] dark:text-[#8ab4f8] rounded-full text-[10px] font-bold uppercase tracking-wider">Lead</span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-[#e6f4ea] text-[#137333] dark:bg-[#072711] dark:text-[#81c995] rounded-full text-[10px] font-bold uppercase tracking-wider">Thành Viên</span>
                                @endif
                            </div>
                        </div>

                        <div class="py-1">
                            @if(auth()->user()?->isAdmin())
                                <a href="{{ route('members.index') }}" class="px-4 py-2.5 hover:bg-[#f1f3f4] dark:hover:bg-[#282a2c] flex items-center space-x-2.5 transition text-[#1a73e8] dark:text-[#8ab4f8] font-medium">
                                    <span>👥 Quản lý người dùng</span>
                                </a>
                            @endif
                            <a href="{{ route('password.change') }}" class="px-4 py-2.5 hover:bg-[#f1f3f4] dark:hover:bg-[#282a2c] flex items-center space-x-2.5 transition text-[#3c4043] dark:text-[#bdc1c6]">
                                <span>🔐 Đổi mật khẩu</span>
                            </a>
                        </div>

                        <div class="pt-2 border-t border-[#f1f3f4] dark:border-[#282a2c] px-3">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-center py-2 text-[#c5221f] dark:text-[#f28b82] hover:bg-[#fce8e6] dark:hover:bg-[#3c1211] rounded-full font-bold transition cursor-pointer">
                                    Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @else
                <div class="flex items-center space-x-2">
                    <a href="{{ route('login') }}" class="px-4 py-2 bg-[#1a73e8] hover:bg-[#1557b0] text-white rounded-full font-bold text-xs shadow-sm transition-all flex items-center space-x-1.5 cursor-pointer">
                        <span>Đăng nhập</span>
                    </a>
                </div>
                @endauth

                <!-- Mobile Hamburger Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden w-9 h-9 rounded-full flex items-center justify-center text-[#5f6368] dark:text-[#9aa0a6] hover:bg-[#f1f3f4] dark:hover:bg-[#282a2c] transition-colors">
                    <span x-show="!mobileMenuOpen" class="text-lg">☰</span>
                    <span x-show="mobileMenuOpen" class="text-lg">✕</span>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-t border-[#dadce0] dark:border-[#282a2c] bg-white dark:bg-[#1e1f20] px-4 py-3 space-y-2 text-xs font-bold text-[#3c4043] dark:text-[#e3e3e3] shadow-lg">
            <a href="{{ route('dashboard') }}" @click="mobileMenuOpen = false" class="block py-2 px-3 rounded-2xl hover:bg-[#f1f3f4] dark:hover:bg-[#282a2c] {{ request()->routeIs('dashboard') ? 'bg-[#c2e7ff] text-[#001d35] dark:bg-[#004a77] dark:text-[#c2e7ff]' : '' }}">
                📊 Tổng Quan
            </a>
            <a href="{{ route('history') }}" @click="mobileMenuOpen = false" class="block py-2 px-3 rounded-2xl hover:bg-[#f1f3f4] dark:hover:bg-[#282a2c] {{ request()->routeIs('history') ? 'bg-[#c2e7ff] text-[#001d35] dark:bg-[#004a77] dark:text-[#c2e7ff]' : '' }}">
                📝 Lịch Sử Giao Dịch
            </a>
            <a href="{{ route('projects.index') }}" @click="mobileMenuOpen = false" class="block py-2 px-3 rounded-2xl hover:bg-[#f1f3f4] dark:hover:bg-[#282a2c] {{ request()->routeIs('projects.*') ? 'bg-[#c2e7ff] text-[#001d35] dark:bg-[#004a77] dark:text-[#c2e7ff]' : '' }}">
                📁 Quản Lý Dự Án
            </a>
            <a href="{{ route('analytics.networth') }}" @click="mobileMenuOpen = false" class="block py-2 px-3 rounded-2xl hover:bg-[#f1f3f4] dark:hover:bg-[#282a2c] {{ request()->routeIs('analytics.*') ? 'bg-[#c2e7ff] text-[#001d35] dark:bg-[#004a77] dark:text-[#c2e7ff]' : '' }}">
                📈 Tài Sản & Net Worth
            </a>
        </div>
    </header>

    <!-- Main Container Canvas -->
    <main class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 py-6 sm:py-8 flex-grow w-full pb-20 sm:pb-8">
        
        <!-- Google Toast Notifications -->
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
                 class="fixed bottom-6 right-6 z-50 bg-[#323232] dark:bg-[#e3e3e3] text-white dark:text-[#131314] px-5 py-3 rounded-full shadow-2xl flex items-center space-x-3 text-xs font-semibold max-w-sm">
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="ml-auto text-[#8ab4f8] dark:text-[#1a73e8] font-bold text-xs uppercase cursor-pointer">Đóng</button>
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
                 class="fixed bottom-6 right-6 z-50 bg-[#c5221f] text-white px-5 py-3 rounded-full shadow-2xl flex items-center space-x-3 text-xs font-semibold max-w-sm">
                <span>⚠️ {{ session('error') }}</span>
                <button @click="show = false" class="ml-auto text-white/80 hover:text-white font-bold text-xs uppercase cursor-pointer">✕</button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bottom Mobile Navigation Bar (Material 3 Pill Bottom Bar) -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-[#1e1f20]/95 backdrop-blur-lg border-t border-[#dadce0] dark:border-[#282a2c] px-3 py-1.5 shadow-2xl safe-bottom">
        <div class="grid grid-cols-4 gap-1 max-w-md mx-auto">
            
            <!-- Tab 1: Tổng Quan -->
            <a href="{{ route('dashboard') }}" 
               class="flex flex-col items-center justify-center py-1.5 px-0.5 rounded-2xl transition cursor-pointer {{ request()->routeIs('dashboard') ? 'bg-[#c2e7ff] text-[#001d35] dark:bg-[#004a77] dark:text-[#c2e7ff] font-bold' : 'text-[#5f6368] dark:text-[#9aa0a6] hover:text-[#202124] dark:hover:text-white' }}">
                <span class="text-base leading-none mb-0.5">📊</span>
                <span class="text-[10px] tracking-tight">Tổng Quan</span>
            </a>

            <!-- Tab 2: Giao Dịch -->
            <a href="{{ route('history') }}" 
               class="flex flex-col items-center justify-center py-1.5 px-0.5 rounded-2xl transition cursor-pointer {{ request()->routeIs('history') ? 'bg-[#c2e7ff] text-[#001d35] dark:bg-[#004a77] dark:text-[#c2e7ff] font-bold' : 'text-[#5f6368] dark:text-[#9aa0a6] hover:text-[#202124] dark:hover:text-white' }}">
                <span class="text-base leading-none mb-0.5">📝</span>
                <span class="text-[10px] tracking-tight">Giao Dịch</span>
            </a>

            <!-- Tab 3: Dự Án -->
            <a href="{{ route('projects.index') }}" 
               class="flex flex-col items-center justify-center py-1.5 px-0.5 rounded-2xl transition cursor-pointer {{ request()->routeIs('projects.*') ? 'bg-[#c2e7ff] text-[#001d35] dark:bg-[#004a77] dark:text-[#c2e7ff] font-bold' : 'text-[#5f6368] dark:text-[#9aa0a6] hover:text-[#202124] dark:hover:text-white' }}">
                <span class="text-base leading-none mb-0.5">📁</span>
                <span class="text-[10px] tracking-tight">Dự Án</span>
            </a>

            <!-- Tab 4: Tài Sản -->
            <a href="{{ route('analytics.networth') }}" 
               class="flex flex-col items-center justify-center py-1.5 px-0.5 rounded-2xl transition cursor-pointer {{ request()->routeIs('analytics.*') ? 'bg-[#c2e7ff] text-[#001d35] dark:bg-[#004a77] dark:text-[#c2e7ff] font-bold' : 'text-[#5f6368] dark:text-[#9aa0a6] hover:text-[#202124] dark:hover:text-white' }}">
                <span class="text-base leading-none mb-0.5">📈</span>
                <span class="text-[10px] tracking-tight">Tài Sản</span>
            </a>

        </div>
    </div>

    <!-- Minimalist Google Footer -->
    <footer class="hidden md:block bg-white dark:bg-[#1e1f20] border-t border-[#dadce0] dark:border-[#282a2c] py-4 mt-auto transition-colors">
        <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 flex items-center justify-between text-xs text-[#5f6368] dark:text-[#9aa0a6]">
            <div class="flex items-center space-x-2">
                <span class="font-bold text-[#202124] dark:text-[#e3e3e3]">Weamis Money</span>
                <span>•</span>
                <span>Hệ thống quản lý tài chính minh bạch cho team Weamis</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('dashboard') }}" class="hover:text-[#1a73e8] dark:hover:text-[#8ab4f8] transition">Tổng Quan</a>
                <a href="{{ route('projects.index') }}" class="hover:text-[#1a73e8] dark:hover:text-[#8ab4f8] transition">Dự Án</a>
                <a href="{{ route('analytics.networth') }}" class="hover:text-[#1a73e8] dark:hover:text-[#8ab4f8] transition">Net Worth</a>
                <a href="{{ route('history') }}" class="hover:text-[#1a73e8] dark:hover:text-[#8ab4f8] transition">Lịch Sử GD</a>
                <span>© {{ date('Y') }} Team Weamis</span>
            </div>
        </div>
    </footer>
</body>
</html>
