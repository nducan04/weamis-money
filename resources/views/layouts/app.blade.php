<!DOCTYPE html>
<html lang="vi" x-data="{ 
    darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches), 
    userMenuOpen: false, 
    profileModalOpen: false 
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
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            -webkit-tap-highlight-color: transparent;
            -webkit-font-smoothing: antialiased;
        }
        .dark body {
            background-color: #0f172a;
        }
        /* Mobile bottom-sheet modal slide-up animation */
        [x-cloak] { display: none !important; }
        
        /* Smooth scrolling for modals */
        .overflow-y-auto { -webkit-overflow-scrolling: touch; }

        /* Touch-friendly buttons */
        @media (max-width: 640px) {
            button, a[role="button"], [type="submit"] {
                min-height: 40px;
            }
            select, input[type="text"], input[type="number"], input[type="email"] {
                font-size: 16px !important; /* Prevents iOS zoom on focus */
            }
        }

        /* Safe area padding for notched phones */
        @supports (padding: max(0px)) {
            .safe-bottom { padding-bottom: max(1rem, env(safe-area-inset-bottom)); }
            .safe-top { padding-top: max(0.875rem, env(safe-area-inset-top)); }
        }

        /* Line clamp utility for mobile cards */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="text-slate-800 dark:text-slate-100 min-h-screen flex flex-col transition-colors duration-200">

    <!-- Top Navigation Header (Vibrant Emerald Brand Navbar) -->
    <header class="bg-gradient-to-r from-emerald-300 via-emerald-400 to-teal-700 text-white sticky top-0 z-30 shadow-md border-b border-emerald-800/50 safe-top transition-colors">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 py-2.5 sm:py-3.5 flex items-center justify-between">
            <div class="flex items-center space-x-2.5 sm:space-x-3 min-w-0">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 sm:space-x-3 min-w-0">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-white text-emerald-700 font-black text-base sm:text-xl flex items-center justify-center shadow-md flex-shrink-0">
                        💲
                    </div>
                    <div class="min-w-0">
                        <h1 class="font-black text-base sm:text-xl tracking-tight leading-none text-white truncate">Weamis Money</h1>
                        <p class="text-[10px] sm:text-xs text-emerald-100 font-semibold hidden xs:block">Quản lý thu chi & Quỹ team</p>
                    </div>
                </a>
            </div>
            
            <div class="flex items-center space-x-2 sm:space-x-3 flex-shrink-0">

                <!-- Dark Mode Toggle -->
                <button @click="darkMode = !darkMode" class="p-2 sm:p-2.5 rounded-xl bg-emerald-800/60 hover:bg-emerald-800 border border-emerald-500/40 text-xs font-bold text-white flex items-center space-x-1.5 transition">
                    <span x-show="!darkMode">🌙</span>
                    <span x-show="darkMode">☀️</span>
                    <span class="hidden sm:inline" x-show="!darkMode">Tối</span>
                    <span class="hidden sm:inline" x-show="darkMode">Sáng</span>
                </button>

                @auth
                    <!-- User Profile Dropdown & Modal -->
                    <div class="relative pl-2 border-l border-emerald-500/40">
                        <!-- Clickable Avatar Tab -->
                        <button @click="userMenuOpen = !userMenuOpen" class="flex items-center space-x-2 sm:space-x-2.5 bg-emerald-800/70 hover:bg-emerald-800 border border-emerald-500/50 px-2.5 sm:px-3 py-1.5 rounded-2xl transition shadow-sm focus:outline-none cursor-pointer">
                            <div class="w-7 h-7 rounded-full bg-white text-emerald-800 font-black text-xs flex items-center justify-center flex-shrink-0 shadow-sm overflow-hidden border border-white/20">
                                @if(Auth::user()->avatar && (\Illuminate\Support\Str::startsWith(Auth::user()->avatar, ['http://', 'https://', '/uploads/'])))
                                    <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                                @else
                                    {{ Auth::user()->avatar ?? substr(Auth::user()->name, 0, 2) }}
                                @endif
                            </div>
                            <div class="hidden sm:block text-left">
                                <p class="text-xs font-extrabold text-white leading-none flex items-center space-x-1">
                                    <span>{{ Auth::user()->name }}</span>
                                    <svg class="w-3.5 h-3.5 text-emerald-200 transition-transform duration-200" :class="userMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                </p>
                                <span class="text-[9px] font-bold text-emerald-200">
                                    {{ Auth::user()->role === 'admin' ? 'Chủ quỹ' : 'Thành viên' }}
                                </span>
                            </div>
                        </button>

                        <!-- Dropdown Menu Box -->
                        <div x-show="userMenuOpen" 
                             @click.away="userMenuOpen = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                             x-cloak
                             class="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 py-2 z-50 text-slate-800 dark:text-slate-100">
                            
                            <!-- Header User Brief -->
                            <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-700/30 rounded-t-2xl">
                                <p class="text-xs font-extrabold text-slate-900 dark:text-white truncate">{{ Auth::user()->name }}</p>
                                <p class="text-[11px] font-medium text-slate-400 truncate mt-0.5">{{ Auth::user()->email }}</p>
                                <div class="mt-1.5 flex items-center space-x-2">
                                    <span class="text-[9px] font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 px-2 py-0.5 rounded-full uppercase tracking-wider">
                                        {{ Auth::user()->role === 'admin' ? 'Chủ quỹ (Admin)' : 'Thành viên' }}
                                    </span>
                                    <span class="text-[9px] font-bold text-slate-400">Cổ phần: {{ Auth::user()->share_percentage }}%</span>
                                </div>
                            </div>

                            <!-- Menu Action Items -->
                            <div class="py-1">
                                <!-- Item 1: Update Profile -->
                                <button @click="userMenuOpen = false; profileModalOpen = true" class="w-full text-left px-4 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center space-x-2.5 transition cursor-pointer">
                                    <span class="p-1 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300">👤</span>
                                    <span>Thông tin cá nhân</span>
                                </button>

                                <!-- Item 2: Change Password -->
                                <a href="{{ route('password.change') }}" class="w-full text-left px-4 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center space-x-2.5 transition">
                                    <span class="p-1 rounded-lg bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300">🔑</span>
                                    <span>Đổi mật khẩu</span>
                                </a>
                            </div>

                            <div class="border-t border-slate-100 dark:border-slate-700 pt-1 mt-1">
                                <!-- Item 3: Logout -->
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2.5 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 flex items-center space-x-2.5 transition cursor-pointer">
                                        <span class="p-1 rounded-lg bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300">🚪</span>
                                        <span>Đăng xuất</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('login') }}" class="px-3.5 py-2 bg-white text-emerald-700 rounded-xl text-xs font-extrabold hover:bg-emerald-50 transition shadow-md">Đăng Nhập</a>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-3 sm:px-6 py-4 sm:py-6 flex-1 w-full">
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
                    <p class="font-extrabold text-[10px] uppercase tracking-wider text-rose-100">Cảnh báo</p>
                    <p class="font-bold text-xs sm:text-sm leading-snug">{{ session('error') }}</p>
                </div>
                <button @click="show = false" class="text-white/80 hover:text-white text-base font-bold px-1.5 cursor-pointer">✕</button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Soft Neutral Slate Footer (PrebuiltUI Inspired) -->
    <footer class="bg-slate-100 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 pt-12 px-4 sm:px-6 md:px-12 lg:px-20 xl:px-28 mt-12 safe-bottom transition-colors">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-wrap justify-between gap-10 md:gap-6">
                <!-- Column 1: Brand & Info -->
                <div class="max-w-xs">
                    <div class="flex items-center space-x-2.5 mb-4">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-black text-lg flex items-center justify-center shadow-md">
                            💲
                        </div>
                        <span class="font-black text-xl text-slate-900 dark:text-white tracking-tight">weamis-money</span>
                    </div>
                    <p class="text-xs sm:text-sm leading-relaxed text-slate-600 dark:text-slate-400 font-medium">
                        Hệ thống quản lý thu chi, vay trả nợ cá nhân và phân chia lợi nhuận theo % cổ phần chuyên nghiệp cho team Weamis.
                    </p>
                    <div class="flex items-center gap-4 mt-5 text-slate-500 dark:text-slate-400">
                        <!-- Instagram -->
                        <a href="#" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition" aria-label="Instagram">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M7.75 2A5.75 5.75 0 002 7.75v8.5A5.75 5.75 0 007.75 22h8.5A5.75 5.75 0 0022 16.25v-8.5A5.75 5.75 0 0016.25 2h-8.5zM4.5 7.75A3.25 3.25 0 017.75 4.5h8.5a3.25 3.25 0 013.25 3.25v8.5a3.25 3.25 0 01-3.25 3.25h-8.5a3.25 3.25 0 01-3.25-3.25v-8.5zm9.5 1a4 4 0 11-4 4 4 4 0 014-4zm0 1.5a2.5 2.5 0 102.5 2.5 2.5 2.5 0 00-2.5-2.5zm3.5-.75a.75.75 0 11.75-.75.75.75 0 01-.75.75z" />
                            </svg>
                        </a>
                        <!-- Facebook -->
                        <a href="#" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition" aria-label="Facebook">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13.5 9H15V6.5h-1.5c-1.933 0-3.5 1.567-3.5 3.5v1.5H8v3h2.5V21h3v-7.5H16l.5-3h-3z" />
                            </svg>
                        </a>
                        <!-- Twitter -->
                        <a href="#" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition" aria-label="Twitter">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22 5.92a8.2 8.2 0 01-2.36.65A4.1 4.1 0 0021.4 4a8.27 8.27 0 01-2.6 1A4.14 4.14 0 0016 4a4.15 4.15 0 00-4.15 4.15c0 .32.04.64.1.94a11.75 11.75 0 01-8.52-4.32 4.14 4.14 0 001.29 5.54A4.1 4.1 0 013 10v.05a4.15 4.15 0 003.33 4.07 4.12 4.12 0 01-1.87.07 4.16 4.16 0 003.88 2.89A8.33 8.33 0 012 19.56a11.72 11.72 0 006.29 1.84c7.55 0 11.68-6.25 11.68-11.67 0-.18 0-.35-.01-.53A8.18 8.18 0 0022 5.92z" />
                            </svg>
                        </a>
                        <!-- LinkedIn -->
                        <a href="#" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition" aria-label="LinkedIn">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M4.98 3.5C3.88 3.5 3 4.38 3 5.48c0 1.1.88 1.98 1.98 1.98h.02c1.1 0 1.98-.88 1.98-1.98C6.98 4.38 6.1 3.5 4.98 3.5zM3 8.75h3.96V21H3V8.75zm6.25 0h3.8v1.68h.05c.53-.98 1.82-2.02 3.75-2.02 4.01 0 4.75 2.64 4.75 6.07V21H17v-5.63c0-1.34-.03-3.07-1.88-3.07-1.88 0-2.17 1.47-2.17 2.98V21H9.25V8.75z" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Column 2: CÔNG TY -->
                <div>
                    <p class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">CÔNG TY</p>
                    <ul class="mt-4 flex flex-col gap-2.5 text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-400">
                        <li><a href="#" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Về Weamis</a></li>
                        <li><a href="#" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Tuyển dụng</a></li>
                        <li><a href="#" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Báo chí</a></li>
                        <li><a href="#" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Blog tài chính</a></li>
                        <li><a href="#" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Đối tác</a></li>
                    </ul>
                </div>

                <!-- Column 3: HỖ TRỢ -->
                <div>
                    <p class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">HỖ TRỢ</p>
                    <ul class="mt-4 flex flex-col gap-2.5 text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-400">
                        <li><a href="#" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Trung tâm trợ giúp</a></li>
                        <li><a href="#" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Thông tin an toàn</a></li>
                        <li><a href="#" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Chính sách hủy</a></li>
                        <li><a href="#" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Liên hệ chúng tôi</a></li>
                        <li><a href="#" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Hỗ trợ tiếp cận</a></li>
                    </ul>
                </div>

                <!-- Column 4: ĐĂNG KÝ NHẬN TIN -->
                <div class="max-w-xs">
                    <p class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">ĐĂNG KÝ NHẬN TIN</p>
                    <p class="mt-4 text-xs sm:text-sm leading-relaxed text-slate-600 dark:text-slate-400 font-medium">
                        Đăng ký nhận bản tin cập nhật biến động quỹ và báo cáo thu chi hàng tháng.
                    </p>
                    <div class="flex items-center mt-4">
                        <input type="text" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-l-xl h-10 px-3.5 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-emerald-500 outline-none text-slate-900 dark:text-white w-full placeholder-slate-400" placeholder="Your email" />
                        <button class="flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white font-bold h-10 w-10 aspect-square rounded-r-xl transition flex-shrink-0">
                            <!-- Arrow icon -->
                            <svg class="w-4 h-4 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 12H5m14 0-4 4m4-4-4-4" /></svg>
                        </button>
                    </div>
                </div>
            </div>

            <hr class="border-slate-200 dark:border-slate-800 mt-10" />

            <div class="flex flex-col md:flex-row gap-3 items-center justify-between py-6 text-xs font-semibold text-slate-500 dark:text-slate-400">
                <p>© {{ date('Y') }} <a href="#" class="text-emerald-600 dark:text-emerald-400 font-bold hover:underline">Weamis Money</a>. Tất cả các quyền được bảo lưu.</p>
                <ul class="flex items-center gap-5">
                    <li><a href="#" class="hover:text-slate-900 dark:hover:text-white transition">Quyền riêng tư</a></li>
                    <li><a href="#" class="hover:text-slate-900 dark:hover:text-white transition">Điều khoản</a></li>
                    <li><a href="#" class="hover:text-slate-900 dark:hover:text-white transition">Sitemap</a></li>
                </ul>
            </div>
        </div>
    </footer>
    @auth
        <!-- Profile Edit Modal -->
        <div x-show="profileModalOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            
            <div @click.away="profileModalOpen = false" 
                 class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 relative">
                
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300 font-extrabold text-lg flex items-center justify-center">
                            👤
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Cập Nhật Thông Tin</h3>
                            <p class="text-xs text-slate-400">Thay đổi tên, email và chữ đại diện</p>
                        </div>
                    </div>
                    <button @click="profileModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg font-bold cursor-pointer">✕</button>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4" x-data="{ avatarPreview: '{{ Auth::user()->avatar }}' }">
                    @csrf
                    
                    <!-- Clickable Avatar Box (Triggers File Picker) -->
                    <div class="flex flex-col items-center justify-center py-2">
                        <div @click="$refs.avatarFileInput.click()" 
                             class="w-24 h-24 rounded-3xl bg-slate-900 text-emerald-400 font-black text-2xl flex items-center justify-center shadow-lg overflow-hidden border-4 border-emerald-500/30 relative group cursor-pointer transition transform hover:scale-105">
                            
                            <template x-if="avatarPreview && (avatarPreview.startsWith('http') || avatarPreview.startsWith('/uploads/') || avatarPreview.startsWith('data:image'))">
                                <img :src="avatarPreview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!avatarPreview || (!avatarPreview.startsWith('http') && !avatarPreview.startsWith('/uploads/') && !avatarPreview.startsWith('data:image'))">
                                <span x-text="avatarPreview || '{{ substr(Auth::user()->name, 0, 2) }}'"></span>
                            </template>

                            <!-- Hover Camera Overlay -->
                            <div class="absolute inset-0 bg-slate-950/75 opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center transition-opacity duration-200 text-white">
                                <svg class="w-6 h-6 mb-1 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="text-[10px] font-extrabold tracking-tight">Tải ảnh lên</span>
                            </div>
                        </div>

                        <!-- Hidden File Input -->
                        <input type="file" x-ref="avatarFileInput" name="avatar_file" accept="image/*" class="hidden"
                               @change="const file = $event.target.files[0]; if(file) { const reader = new FileReader(); reader.onload = (e) => avatarPreview = e.target.result; reader.readAsDataURL(file); }">
                    </div>

                    <!-- Full Name -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Họ và Tên</label>
                        <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-semibold focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Địa Chỉ Email</label>
                        <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-semibold focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white">
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                        <button type="button" @click="profileModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer">Hủy</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/30 transition cursor-pointer">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    @endauth
</body>
</html>
