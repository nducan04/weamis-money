<!DOCTYPE html>
<html lang="vi" x-data="{ darkMode: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#059669">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>{{ config('app.name', 'Weamis Money') }} - Quản Lý Thu Chi Team</title>
    
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
                        <h1 class="font-black text-base sm:text-xl tracking-tight leading-none text-white truncate">weamis-money</h1>
                        <p class="text-[10px] sm:text-xs text-emerald-100 font-semibold hidden xs:block">Quản lý thu chi & Quỹ team</p>
                    </div>
                </a>
            </div>
            
            <div class="flex items-center space-x-2 sm:space-x-3 flex-shrink-0">
                <span class="hidden lg:inline-flex items-center px-3 py-1 bg-emerald-800/60 border border-emerald-500/40 text-emerald-100 text-xs font-bold rounded-full backdrop-blur-sm">
                    🏢 Quỹ: Trả nợ thuê Ltd
                </span>

                <!-- Dark Mode Toggle -->
                <button @click="darkMode = !darkMode" class="p-2 sm:p-2.5 rounded-xl bg-emerald-800/60 hover:bg-emerald-800 border border-emerald-500/40 text-xs font-bold text-white flex items-center space-x-1.5 transition">
                    <span x-show="!darkMode">🌙</span>
                    <span x-show="darkMode">☀️</span>
                    <span class="hidden sm:inline" x-show="!darkMode">Tối</span>
                    <span class="hidden sm:inline" x-show="darkMode">Sáng</span>
                </button>

                @auth
                    <!-- User Profile & Logout -->
                    <div class="flex items-center space-x-2 pl-2 border-l border-emerald-500/40">
                        <div class="flex items-center space-x-2 bg-emerald-800/60 border border-emerald-500/40 px-2.5 py-1.5 rounded-xl">
                            <div class="w-6.5 h-6.5 rounded-full bg-white text-emerald-800 font-black text-[10px] flex items-center justify-center flex-shrink-0">
                                {{ Auth::user()->avatar ?? substr(Auth::user()->name, 0, 2) }}
                            </div>
                            <div class="hidden sm:block text-left">
                                <p class="text-xs font-extrabold text-white leading-none">{{ Auth::user()->name }}</p>
                                <span class="text-[9px] font-bold text-emerald-200">
                                    {{ Auth::user()->role === 'admin' ? 'Chủ quỹ' : 'Thành viên' }}
                                </span>
                            </div>
                        </div>

                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" title="Đăng xuất" class="p-2 bg-emerald-800/60 hover:bg-rose-600 text-white border border-emerald-500/40 rounded-xl text-xs font-bold transition flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            </button>
                        </form>
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
</body>
</html>
