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
<body class="text-slate-800 dark:text-slate-100 min-h-screen flex flex-col transition-colors duration-200">

    <!-- Top Navigation Header (Vibrant Emerald Brand Navbar) -->
    <header class="bg-gradient-to-r from-emerald-500 via-emerald-600 to-teal-700 text-white sticky top-0 z-30 shadow-md border-b border-emerald-800/50 safe-top transition-colors">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 py-2.5 sm:py-3.5 flex items-center justify-between">
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
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white shadow-inner' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                        <span>📊 Dashboard</span>
                    </a>
                    <a href="{{ route('history') }}" 
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 {{ request()->routeIs('history') ? 'bg-white/20 text-white shadow-inner' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                        <span>📜 Lịch Sử Giao Dịch</span>
                    </a>
                </nav>
            </div>
            
            <div class="flex items-center space-x-2 sm:space-x-3 flex-shrink-0">
                <!-- Dark Mode Toggle -->
                <button @click="darkMode = !darkMode" class="p-2 sm:p-2.5 rounded-xl bg-emerald-800/60 hover:bg-emerald-800 border border-emerald-500/40 text-xs font-bold text-white flex items-center space-x-1.5 transition cursor-pointer">
                    <span x-show="!darkMode">🌙</span>
                    <span x-show="darkMode">☀️</span>
                    <span class="hidden sm:inline" x-show="!darkMode">Chế độ Tối</span>
                    <span class="hidden sm:inline" x-show="darkMode">Chế độ Sáng</span>
                </button>
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
                    <p class="font-extrabold text-[10px] uppercase tracking-wider text-rose-100">Lỗi</p>
                    <p class="font-bold text-xs sm:text-sm leading-snug">{{ session('error') }}</p>
                </div>
                <button @click="show = false" class="text-white/80 hover:text-white text-base font-bold px-1.5 cursor-pointer">✕</button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 transition-colors mt-auto">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 pt-10 pb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- Column 1: Brand info -->
                <div class="space-y-3 md:col-span-1">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-7 h-7 rounded-lg bg-emerald-600 text-white font-black text-sm flex items-center justify-center shadow-sm">
                            💲
                        </div>
                        <span class="font-black text-lg text-slate-900 dark:text-white tracking-tight">Weamis Money</span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-medium">
                        Giải pháp quản lý thu chi, phân bổ lợi nhuận và theo dõi dư nợ quỹ nhóm minh bạch, chính xác.
                    </p>
                    <div class="flex items-center space-x-3 text-slate-400 pt-1">
                        <!-- Facebook -->
                        <a href="#" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition" aria-label="Facebook">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H7.5v-3H10V9.5C10 7.01 11.49 5.6 13.78 5.6c1.1 0 2.25.2 2.25.2v2.48h-1.27c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.45 3h-2.33v6.8c4.56-.93 8-4.96 8-9.8z" />
                            </svg>
                        </a>
                        <!-- Twitter/X -->
                        <a href="#" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition" aria-label="Twitter">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
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
</body>
</html>
