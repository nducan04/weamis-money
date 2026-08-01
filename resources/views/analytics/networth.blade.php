@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Túi Thần Tài Widget Card -->
    @if($fund && $fund->total_profit > 0)
    <div class="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent rounded-3xl p-5 border border-amber-500/20 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-2xl bg-amber-500 text-amber-950 font-black text-xl flex items-center justify-center shadow-md shadow-amber-500/20 flex-shrink-0">
                💰
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h4 class="font-extrabold text-base text-slate-900 dark:text-white">Túi Thần Tài</h4>
                    <span class="px-2.5 py-0.5 text-[10px] font-black bg-amber-500/20 text-amber-600 dark:text-amber-400 rounded-full border border-amber-500/30">Quỹ Tích Lũy Sinh Lời</span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-medium">Khoản đầu tư tích lũy sinh lời của Quỹ chung (Khởi tạo 12/02/2026)</p>
            </div>
        </div>
        <div class="text-left sm:text-right bg-white/80 dark:bg-slate-800/80 px-4 py-2.5 rounded-2xl border border-amber-200 dark:border-slate-700 w-full sm:w-auto">
            <p class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Tổng Tích Lũy</p>
            <p class="text-2xl font-black text-amber-600 dark:text-amber-400">
                +{{ number_format($fund->total_profit, 0, ',', '.') }}<span class="text-base font-extrabold">đ</span>
            </p>
        </div>
    </div>
    @endif

    <!-- 1. Ranked Net Worth Member Cards -->
    <div class="space-y-3">
        <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center space-x-2">
            <span>Bảng Xếp Hạng Tài Sản Ròng Thành Viên</span>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($netWorthData as $rank => $nw)
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm relative overflow-hidden flex flex-col justify-between hover:shadow-lg transition-all duration-200">
                    
                    <!-- Rank Badge -->
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-1 text-[11px] font-black rounded-xl {{ $rank === 0 ? 'bg-amber-400 text-amber-950 shadow-sm' : ($rank === 1 ? 'bg-slate-200 text-slate-800' : ($rank === 2 ? 'bg-amber-700/20 text-amber-600' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300')) }}">
                            {{ $rank === 0 ? '👑 TOP 1' : ($rank === 1 ? '🥈 TOP 2' : ($rank === 2 ? '🥉 TOP 3' : '#' . ($rank + 1))) }}
                        </span>
                        <span class="text-xs font-bold text-slate-400">@ {{ $nw['username'] }}</span>
                    </div>

                    <!-- Member Avatar & Name (Safe Multi-byte UTF-8 Initials) -->
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white font-black text-sm flex items-center justify-center flex-shrink-0 overflow-hidden ring-2 ring-emerald-500/30">
                            @if($nw['avatar'] && \Illuminate\Support\Str::startsWith($nw['avatar'], ['http://', 'https://', '/uploads/']))
                                <img src="{{ $nw['avatar'] }}" alt="{{ $nw['name'] }}" class="w-full h-full object-cover">
                            @else
                                @php
                                    $words = explode(' ', trim($nw['name']));
                                    $initials = count($words) >= 2 
                                        ? mb_substr($words[0], 0, 1, 'UTF-8') . mb_substr(end($words), 0, 1, 'UTF-8')
                                        : mb_substr($nw['name'], 0, 2, 'UTF-8');
                                    $initials = mb_strtoupper($initials, 'UTF-8');
                                @endphp
                                {{ $initials }}
                            @endif
                        </div>
                        <div>
                            <h4 class="font-extrabold text-base text-slate-900 dark:text-white leading-snug">{{ $nw['name'] }}</h4>
                            <span class="inline-block mt-0.5 px-2 py-0.5 text-[10px] font-black rounded-lg 
                                {{ str_contains($nw['status_label'], 'Chủ nợ lớn nhất') ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : '' }}
                                {{ str_contains($nw['status_label'], 'Chủ nợ của quỹ') ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400' : '' }}
                                {{ str_contains($nw['status_label'], 'mượn ròng') ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400' : '' }}
                                {{ str_contains($nw['status_label'], 'âm ròng nhiều nhất') ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400' : '' }}">
                                {{ $nw['status_label'] }}
                            </span>
                        </div>
                    </div>

                    <!-- Net Worth Value -->
                    <div class="bg-slate-50 dark:bg-slate-700/40 p-3.5 rounded-2xl border border-slate-100 dark:border-slate-700/60 mb-3 text-center">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Tài Sản Ròng (Vị thế ròng)</p>
                        <p class="text-2xl font-black tracking-tight {{ $nw['net_worth'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ number_format($nw['net_worth'], 0, ',', '.') }}<span class="text-base font-extrabold">đ</span>
                        </p>
                    </div>

                    <!-- Breakdown Detail List -->
                    <div class="space-y-1.5 text-xs border-t border-slate-100 dark:border-slate-700 pt-3">
                        <div class="flex justify-between items-center text-slate-600 dark:text-slate-300">
                            <span class="font-medium">💵 Tổng Góp:</span>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400">+{{ number_format($nw['contributions'], 0, ',', '.') }}đ</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-600 dark:text-slate-300">
                            <span class="font-medium">🔻 Tổng Rút / Vay:</span>
                            <span class="font-bold text-rose-500">-{{ number_format($nw['withdrawals'], 0, ',', '.') }}đ</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
