<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng Nhập | Weamis Money</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(2deg); }
        }
        @keyframes pulseSlow {
            0%, 100% { opacity: 0.15; transform: scale(1); }
            50% { opacity: 0.25; transform: scale(1.08); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-float {
            animation: float 4s ease-in-out infinite;
        }
        .animate-pulse-slow {
            animation: pulseSlow 6s ease-in-out infinite;
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-10 relative overflow-x-hidden">

    <!-- Background glowing ambient lights with smooth pulsing -->
    <div class="absolute -top-40 -left-40 w-[600px] h-[600px] bg-emerald-400/20 rounded-full blur-3xl pointer-events-none animate-pulse-slow"></div>
    <div class="absolute -bottom-40 -right-40 w-[600px] h-[600px] bg-teal-400/20 rounded-full blur-3xl pointer-events-none animate-pulse-slow" style="animation-delay: 3s;"></div>

    <!-- Main Container Card with Entrance Animation -->
    <div class="w-full max-w-5xl bg-white rounded-[36px] border border-slate-200/80 shadow-[0_25px_70px_-15px_rgba(16,185,129,0.12)] overflow-hidden relative z-10 p-4 sm:p-6 lg:p-8 animate-fade-in-up">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            
            <!-- Left Column: Login Form (6 cols on lg) -->
            <div class="lg:col-span-6 px-2 sm:px-6 py-4 space-y-6">

                <!-- Welcome Titles -->
                <div class="space-y-1.5 pt-2">
                    <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight leading-tight">Chào mừng trở lại</h1>
                </div>

                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs font-bold text-emerald-700 animate-fade-in-up">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-2xl text-xs font-bold text-rose-700 animate-fade-in-up">
                        {{ $errors->first() }}
                    </div>
                @endif

                <!-- Login Form -->
                <form action="{{ route('login') }}" method="POST" class="space-y-4" x-data="{ showPassword: false }">
                    @csrf
                    
                    <!-- Username/Email Field -->
                    <div>
                        <label class="block text-xs font-extrabold text-slate-700 mb-1.5">Tên đăng nhập</label>
                        <input type="text" name="login" value="{{ old('login') }}" required placeholder="username" 
                               class="w-full px-4 py-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-sm font-bold text-slate-900 placeholder:text-slate-400 placeholder:font-normal focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white outline-none transition-all duration-200">
                    </div>

                    <!-- Password Field with Show/Hide Toggle -->
                    <div>
                        <label class="block text-xs font-extrabold text-slate-700 mb-1.5">Mật khẩu</label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" name="password" required placeholder="••••••••" 
                                   class="w-full px-4 py-3.5 pr-12 rounded-2xl bg-slate-50 border border-slate-200 text-sm font-bold text-slate-900 placeholder:text-slate-400 placeholder:font-normal focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white outline-none transition-all duration-200">
                            
                            <!-- Password Toggle Eye Button -->
                            <button type="button" @click="showPassword = !showPassword" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition cursor-pointer p-1">
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.049 10.049 0 013.682-.963c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m-4.692-4.692a3 3 0 00-4.243-4.243"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center space-x-2 text-xs font-semibold text-slate-600 cursor-pointer">
                            <input type="checkbox" name="remember" checked class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 bg-slate-100 border-slate-300">
                            <span>Ghi nhớ đăng nhập</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline">Quên mật khẩu?</a>
                    </div>

                    <!-- Login Submit Button -->
                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-500 hover:to-teal-500 active:scale-[0.98] text-white font-extrabold text-sm rounded-2xl shadow-xl shadow-emerald-600/30 hover:shadow-emerald-600/50 transition-all duration-200 cursor-pointer flex items-center justify-center space-x-2 mt-2">
                        <span>Đăng Nhập ➔</span>
                    </button>
                </form>
            </div>

            <!-- Right Column: Vibrant Emerald Hero Card with Floating 3D Logo -->
            <div class="lg:col-span-6 hidden lg:block">
                <div class="w-full h-[550px] bg-gradient-to-br from-emerald-600 via-teal-600 to-emerald-800 rounded-[48px] rounded-tl-[100px] rounded-br-[100px] p-8 text-white relative overflow-hidden flex flex-col justify-between shadow-2xl shadow-emerald-600/20 border border-emerald-400/30">
                    
                    <!-- Decorative Background Gradient Orbs -->
                    <div class="absolute -top-20 -right-20 w-72 h-72 bg-white/20 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-20 -left-20 w-72 h-72 bg-teal-300/20 rounded-full blur-3xl pointer-events-none"></div>

                    <!-- Center Floating 3D Weamis Logo Display -->
                    <div class="relative z-10 flex flex-col items-center justify-center my-auto text-center space-y-6">
                        
                        <!-- Floating Logo Container -->
                        <div class="relative animate-float">
                            <div class="w-36 h-36 rounded-3xl bg-gradient-to-tr from-white via-emerald-50 to-teal-100 text-emerald-600 font-black text-7xl flex items-center justify-center shadow-2xl shadow-black/30 ring-8 ring-white/20">
                                💲
                            </div>
                            <div class="absolute -bottom-4 left-1/2 -translate-x-1/2 w-28 h-4 bg-emerald-900/40 rounded-full blur-md -z-10"></div>
                        </div>

                        <div class="space-y-1.5 max-w-xs">
                            <h3 class="text-2xl font-black text-white tracking-tight drop-shadow-sm">Weamis Money</h3>
                            <p class="text-xs font-semibold text-emerald-100/90 leading-relaxed">Nền tảng quản lý tài chính & quỹ nhóm nội bộ chuyên nghiệp.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</body>
</html>
