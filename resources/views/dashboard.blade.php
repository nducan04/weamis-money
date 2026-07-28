@extends('layouts.app')

@section('content')
<div x-data="{ 
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    showDistributionModal: false,
    showMemberModal: false,
    showFilters: false,
    selectedTx: null,
    distributeAmount: {{ $fund->balance }},
    get calculatedPayouts() {
        let amount = parseFloat(this.distributeAmount) || 0;
        return [
            @foreach($members as $m)
            { id: {{ $m->id }}, name: '{{ $m->name }}', avatar: '{{ $m->avatar }}', share: {{ $m->share_percentage }}, amount: Math.round(amount * {{ $m->share_percentage }} / 100) },
            @endforeach
        ];
    }
}">

    <!-- Page Header & Action Bar -->
    <div class="flex flex-col gap-3 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                Báo Cáo Thu Chi & Quản Lý Quỹ
            </h2>
            <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Theo dõi tổng quan tài chính, danh sách giao dịch thu chi và phân chia tiền theo % cổ phần.
            </p>
        </div>

        <!-- Mobile: full-width stacked buttons / Desktop: inline -->
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2">
            <button @click="showAddModal = true" class="col-span-2 sm:col-span-1 px-4 py-3 sm:py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Thêm Thu Chi Mới</span>
            </button>

            <button @click="showDistributionModal = true" class="px-4 py-3 sm:py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold text-xs rounded-xl shadow-sm transition flex items-center justify-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span>Chia % Quỹ</span>
            </button>

            <button @click="showMemberModal = true" class="px-3.5 py-3 sm:py-2.5 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 font-bold text-xs rounded-xl transition flex items-center justify-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span>Thành Viên ({{ $members->count() }})</span>
            </button>
        </div>
    </div>

    <!-- 1. Top Stat Cards Row (2 cols on mobile, 4 cols on desktop) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <!-- Card 1: Số Dư Quỹ -->
        <div class="col-span-2 sm:col-span-1 bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Số Dư Quỹ</span>
                <span class="p-1.5 sm:p-2 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300">💵</span>
            </div>
            <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white mt-1.5">
                {{ number_format($fund->balance, 0, ',', '.') }}<span class="text-base sm:text-lg font-bold">đ</span>
            </p>
            <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5">
                Lợi nhuận: <strong class="text-emerald-600 dark:text-emerald-400">{{ number_format($fund->total_profit, 0, ',', '.') }}đ</strong>
            </p>
        </div>

        <!-- Card 2: Tổng Thu -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Tổng Thu</span>
                <span class="p-1.5 sm:p-2 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300">📥</span>
            </div>
            <p class="text-lg sm:text-2xl font-extrabold text-blue-600 dark:text-blue-400 mt-1.5">
                +{{ number_format($totalIncome, 0, ',', '.') }}<span class="text-sm sm:text-lg font-bold">đ</span>
            </p>
            <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5">Góp + Trả nợ</p>
        </div>

        <!-- Card 3: Tổng Chi -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Tổng Chi</span>
                <span class="p-1.5 sm:p-2 rounded-xl bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300">📤</span>
            </div>
            <p class="text-lg sm:text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-1.5">
                -{{ number_format($totalExpense, 0, ',', '.') }}<span class="text-sm sm:text-lg font-bold">đ</span>
            </p>
            <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5">Chi tiêu nhóm</p>
        </div>

        <!-- Card 4: Tổng Cho Vay -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Đang Cho Vay</span>
                <span class="p-1.5 sm:p-2 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300">🤝</span>
            </div>
            <p class="text-lg sm:text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1.5">
                {{ number_format($totalLoans, 0, ',', '.') }}<span class="text-sm sm:text-lg font-bold">đ</span>
            </p>
            <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5">Nợ cá nhân chưa trả</p>
        </div>
    </div>

    <!-- Admin Pending Requests Alert Bar -->
    @if($pendingTransactions->count() > 0)
        <div class="mb-6 bg-amber-500/10 border-2 border-amber-500/30 rounded-2xl p-3 sm:p-4">
            <div class="flex items-center space-x-2 mb-3">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                </span>
                <h3 class="font-bold text-xs sm:text-sm text-amber-800 dark:text-amber-300">
                    Có {{ $pendingTransactions->count() }} yêu cầu chờ duyệt
                </h3>
            </div>

            <div class="space-y-2.5">
                @foreach($pendingTransactions as $pending)
                    <div class="p-3 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-amber-200 dark:border-amber-900/40">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-7 h-7 rounded-full bg-amber-100 text-amber-800 font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                    {{ $pending->user->avatar ?? substr($pending->user->name, 0, 2) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-900 dark:text-white truncate">
                                        {{ $pending->user->name }}
                                        <span class="text-[10px] text-slate-400 font-normal">({{ $pending->type === 'expense' ? 'Chi' : 'Vay' }})</span>
                                    </p>
                                    <p class="text-[11px] text-slate-500 truncate">"{{ $pending->description }}"</p>
                                </div>
                            </div>
                            <span class="font-extrabold text-sm text-rose-600 flex-shrink-0 ml-2">-{{ number_format($pending->amount, 0, ',', '.') }}đ</span>
                        </div>
                        <div class="flex items-center justify-end space-x-2">
                            <form action="{{ route('transactions.approve', $pending) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">✓ Duyệt</button>
                            </form>
                            <form action="{{ route('transactions.reject', $pending) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-1.5 bg-slate-200 hover:bg-rose-600 hover:text-white text-slate-700 rounded-lg text-xs font-bold transition">✕ Từ chối</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 2. Member Breakdown: Desktop=Table, Mobile=Cards -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm mb-6">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-700">
            <div>
                <h3 class="font-extrabold text-slate-900 dark:text-white text-sm sm:text-base">Thống Kê Cá Nhân</h3>
                <p class="text-[10px] sm:text-xs text-slate-400">Đóng góp, nợ vay và phần chia % cổ phần</p>
            </div>
        </div>

        <!-- Desktop Table (hidden on mobile) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 uppercase font-bold text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4 rounded-l-xl">Thành viên</th>
                        <th class="py-3 px-4">Cổ phần</th>
                        <th class="py-3 px-4">Đã Góp</th>
                        <th class="py-3 px-4">Đã Rút/Vay</th>
                        <th class="py-3 px-4">Dư Nợ</th>
                        <th class="py-3 px-4 rounded-r-xl">Khi Chia Quỹ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @foreach($memberStats as $stat)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition">
                            <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-7 h-7 rounded-full bg-slate-800 text-emerald-400 font-bold text-xs flex items-center justify-center flex-shrink-0 overflow-hidden">
                                        @if($stat['user']->avatar && \Illuminate\Support\Str::startsWith($stat['user']->avatar, ['http://', 'https://', '/uploads/']))
                                            <img src="{{ $stat['user']->avatar }}" alt="{{ $stat['user']->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ $stat['user']->avatar ?? substr($stat['user']->name, 0, 2) }}
                                        @endif
                                    </div>
                                    <div>
                                        <span>{{ $stat['user']->name }}</span>
                                        @if($stat['user']->role === 'admin')
                                            <span class="ml-1 text-[9px] bg-amber-400 text-slate-950 font-extrabold px-1.5 py-0.5 rounded">Chủ quỹ</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 font-extrabold text-emerald-600 dark:text-emerald-400 text-sm">{{ $stat['share'] }}%</td>
                            <td class="py-3 px-4 font-bold text-emerald-600 dark:text-emerald-400">+{{ number_format($stat['contributed'], 0, ',', '.') }}đ</td>
                            <td class="py-3 px-4 font-bold text-rose-600 dark:text-rose-400">-{{ number_format($stat['withdrawn'], 0, ',', '.') }}đ</td>
                            <td class="py-3 px-4 font-bold">
                                @if($stat['debt'] > 0)
                                    <span class="px-2 py-0.5 bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 rounded font-bold">{{ number_format($stat['debt'], 0, ',', '.') }}đ</span>
                                @else
                                    <span class="text-slate-400">0đ</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-extrabold text-amber-600 dark:text-amber-400 text-sm">{{ number_format($stat['estimated_payout'], 0, ',', '.') }}đ</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards (hidden on desktop) -->
        <div class="md:hidden space-y-3">
            @foreach($memberStats as $stat)
                <div class="p-3.5 bg-slate-50 dark:bg-slate-700/30 rounded-xl border border-slate-100 dark:border-slate-700">
                    <div class="flex items-center justify-between mb-2.5">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-8 h-8 rounded-full bg-slate-800 text-emerald-400 font-bold text-xs flex items-center justify-center flex-shrink-0 overflow-hidden">
                                @if($stat['user']->avatar && \Illuminate\Support\Str::startsWith($stat['user']->avatar, ['http://', 'https://', '/uploads/']))
                                    <img src="{{ $stat['user']->avatar }}" alt="{{ $stat['user']->name }}" class="w-full h-full object-cover">
                                @else
                                    {{ $stat['user']->avatar ?? substr($stat['user']->name, 0, 2) }}
                                @endif
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 dark:text-white flex items-center space-x-1">
                                    <span>{{ $stat['user']->name }}</span>
                                    @if($stat['user']->role === 'admin')
                                        <span class="text-[8px] bg-amber-400 text-slate-950 font-extrabold px-1 rounded">Chủ quỹ</span>
                                    @endif
                                </h4>
                                <p class="text-[10px] text-slate-400">Cổ phần: <strong class="text-emerald-600">{{ $stat['share'] }}%</strong></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] text-slate-400 uppercase font-bold">Chia quỹ</p>
                            <p class="text-sm font-extrabold text-amber-600 dark:text-amber-400">{{ number_format($stat['estimated_payout'], 0, ',', '.') }}đ</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="bg-white dark:bg-slate-800 p-2 rounded-lg">
                            <p class="text-[9px] text-slate-400 font-bold uppercase">Đã góp</p>
                            <p class="text-xs font-extrabold text-emerald-600">+{{ number_format($stat['contributed'], 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-white dark:bg-slate-800 p-2 rounded-lg">
                            <p class="text-[9px] text-slate-400 font-bold uppercase">Đã rút</p>
                            <p class="text-xs font-extrabold text-rose-600">-{{ number_format($stat['withdrawn'], 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-white dark:bg-slate-800 p-2 rounded-lg">
                            <p class="text-[9px] text-slate-400 font-bold uppercase">Dư nợ</p>
                            <p class="text-xs font-extrabold {{ $stat['debt'] > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                {{ $stat['debt'] > 0 ? number_format($stat['debt'], 0, ',', '.') : '0' }}đ
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 3. Transaction History with Filter & Full CRUD -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
        <div class="flex flex-col gap-3 mb-4 pb-4 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-sm sm:text-base">Lịch Sử Thu Chi</h3>
                    <p class="text-[10px] sm:text-xs text-slate-400">Thêm, sửa, xóa lịch sử thu chi tiền của team</p>
                </div>

                <!-- Mobile filter toggle -->
                <button @click="showFilters = !showFilters" class="md:hidden px-3 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold flex items-center space-x-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    <span>Lọc</span>
                </button>
            </div>

            <!-- Filter Bar: always visible on desktop, toggle on mobile -->
            <form action="{{ route('dashboard') }}" method="GET" class="gap-2 grid-cols-1 sm:grid-cols-2 md:flex md:flex-wrap md:items-center" :class="showFilters ? 'grid' : 'hidden md:flex'" x-cloak>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm nội dung..." class="w-full md:w-auto px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-medium focus:ring-2 focus:ring-emerald-500">
                
                <select name="member_id" class="w-full sm:w-auto px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-medium focus:ring-2 focus:ring-emerald-500">
                    <option value="">Tất cả thành viên</option>
                    @foreach($members as $m)
                        <option value="{{ $m->id }}" {{ request('member_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>

                <select name="type" class="w-full sm:w-auto px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-medium focus:ring-2 focus:ring-emerald-500">
                    <option value="">Tất cả loại GD</option>
                    <option value="contribution" {{ request('type') == 'contribution' ? 'selected' : '' }}>Góp quỹ (Thu)</option>
                    <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Chi tiêu (Chi)</option>
                    <option value="loan" {{ request('type') == 'loan' ? 'selected' : '' }}>Vay cá nhân</option>
                    <option value="repayment" {{ request('type') == 'repayment' ? 'selected' : '' }}>Trả nợ vay</option>
                    <option value="distribution" {{ request('type') == 'distribution' ? 'selected' : '' }}>Chia tiền %</option>
                </select>

                <div class="flex items-center gap-2 col-span-full sm:col-span-1">
                    <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                        Lọc
                    </button>
                    @if(request()->hasAny(['search', 'member_id', 'type', 'status']))
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 text-xs font-bold text-rose-600 hover:underline">Xóa lọc</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Desktop Data Table (hidden on mobile) -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 uppercase font-bold text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4 rounded-l-xl">STT</th>
                        <th class="py-3 px-4">Thời Gian</th>
                        <th class="py-3 px-4">Thành Viên</th>
                        <th class="py-3 px-4">Loại GD</th>
                        <th class="py-3 px-4">Số Tiền</th>
                        <th class="py-3 px-4">Nội Dung</th>
                        <th class="py-3 px-4">Trạng Thái</th>
                        <th class="py-3 px-4 text-right rounded-r-xl">Hành Động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-400">{{ method_exists($transactions, 'total') ? ($transactions->total() - (($transactions->currentPage() - 1) * $transactions->perPage() + $loop->index)) : $loop->iteration }}</td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-slate-500">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                <div class="flex items-center space-x-2">
                                    <span class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-[10px] flex items-center justify-center flex-shrink-0 overflow-hidden">
                                        @if($tx->user && $tx->user->avatar && \Illuminate\Support\Str::startsWith($tx->user->avatar, ['http://', 'https://', '/uploads/']))
                                            <img src="{{ $tx->user->avatar }}" alt="{{ $tx->user->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ $tx->user->avatar ?? substr($tx->user->name ?? '', 0, 2) }}
                                        @endif
                                    </span>
                                    <span>{{ $tx->user->name }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($tx->type === 'contribution')
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 font-bold rounded">Góp quỹ</span>
                                @elseif($tx->type === 'expense')
                                    <span class="px-2 py-0.5 bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 font-bold rounded">Chi tiêu</span>
                                @elseif($tx->type === 'loan')
                                    <span class="px-2 py-0.5 bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 font-bold rounded">Vay</span>
                                @elseif($tx->type === 'repayment')
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 font-bold rounded">Trả nợ</span>
                                @elseif($tx->type === 'distribution')
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 font-bold rounded">Chia %</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-extrabold whitespace-nowrap">
                                @if(in_array($tx->type, ['contribution', 'repayment']))
                                    <span class="text-emerald-600 dark:text-emerald-400">+{{ number_format($tx->amount, 0, ',', '.') }}đ</span>
                                @else
                                    <span class="text-slate-900 dark:text-slate-100">-{{ number_format($tx->amount, 0, ',', '.') }}đ</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-700 dark:text-slate-300 max-w-xs truncate">{{ $tx->description }}</td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($tx->status === 'approved')
                                    <span class="text-emerald-600 font-bold">✓ Duyệt</span>
                                @elseif($tx->status === 'pending')
                                    <span class="text-amber-600 font-bold">⏳ Chờ</span>
                                @else
                                    <span class="text-slate-400 font-bold line-through">Hủy</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap space-x-1">
                                <button @click="selectedTx = {{ json_encode($tx) }}; showEditModal = true" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded font-bold transition">✏️ Sửa</button>
                                <button @click="selectedTx = {{ json_encode($tx) }}; showDeleteModal = true" class="px-2.5 py-1 bg-rose-50 dark:bg-rose-900/30 hover:bg-rose-600 hover:text-white text-rose-600 font-bold rounded transition">🗑️ Xóa</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-400">Không tìm thấy giao dịch nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Transaction Cards (hidden on desktop) -->
        <div class="lg:hidden space-y-2.5">
            @forelse($transactions as $tx)
                <div class="p-3.5 bg-slate-50 dark:bg-slate-700/30 rounded-xl border border-slate-100 dark:border-slate-700">
                    <!-- Row 1: User + Amount -->
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-slate-200 font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                {{ $tx->user->avatar ?? substr($tx->user->name, 0, 2) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $tx->user->name }}</p>
                                <p class="text-[10px] text-slate-400">{{ $tx->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0 ml-2">
                            @if(in_array($tx->type, ['contribution', 'repayment']))
                                <p class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400">+{{ number_format($tx->amount, 0, ',', '.') }}đ</p>
                            @else
                                <p class="text-sm font-extrabold text-slate-900 dark:text-slate-100">-{{ number_format($tx->amount, 0, ',', '.') }}đ</p>
                            @endif
                        </div>
                    </div>

                    <!-- Row 2: Badge + Description -->
                    <div class="flex items-start space-x-2 mb-2.5">
                        @if($tx->type === 'contribution')
                            <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 font-bold rounded text-[10px] flex-shrink-0">Góp quỹ</span>
                        @elseif($tx->type === 'expense')
                            <span class="px-1.5 py-0.5 bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 font-bold rounded text-[10px] flex-shrink-0">Chi tiêu</span>
                        @elseif($tx->type === 'loan')
                            <span class="px-1.5 py-0.5 bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 font-bold rounded text-[10px] flex-shrink-0">Vay</span>
                        @elseif($tx->type === 'repayment')
                            <span class="px-1.5 py-0.5 bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 font-bold rounded text-[10px] flex-shrink-0">Trả nợ</span>
                        @elseif($tx->type === 'distribution')
                            <span class="px-1.5 py-0.5 bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 font-bold rounded text-[10px] flex-shrink-0">Chia %</span>
                        @endif
                        <p class="text-[11px] text-slate-600 dark:text-slate-300 font-medium line-clamp-2">{{ $tx->description }}</p>
                    </div>

                    <!-- Row 3: Status + Actions -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 dark:border-slate-600/40">
                        <div>
                            @if($tx->status === 'approved')
                                <span class="text-[10px] text-emerald-600 font-bold">✓ Đã duyệt</span>
                            @elseif($tx->status === 'pending')
                                <span class="text-[10px] text-amber-600 font-bold">⏳ Chờ duyệt</span>
                            @else
                                <span class="text-[10px] text-slate-400 font-bold line-through">Từ chối</span>
                            @endif
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <button @click="selectedTx = {{ json_encode($tx) }}; showEditModal = true" class="px-3 py-1.5 bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-[10px] font-bold active:scale-95 transition">✏️ Sửa</button>
                            <button @click="selectedTx = {{ json_encode($tx) }}; showDeleteModal = true" class="px-3 py-1.5 bg-rose-100 dark:bg-rose-900/30 text-rose-600 rounded-lg text-[10px] font-bold active:scale-95 transition">🗑️ Xóa</button>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center py-8 text-xs text-slate-400">Không tìm thấy giao dịch nào.</p>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700">
            {{ $transactions->links() }}
        </div>
    </div>

    <!-- MODAL: THÊM GIAO DỊCH MỚI -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="showAddModal = false" class="bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl p-5 sm:p-6 w-full sm:max-w-md shadow-2xl border border-slate-100 dark:border-slate-700 max-h-[90vh] overflow-y-auto">
            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-4">➕ Thêm Giao Dịch Thu Chi</h3>

            <form action="{{ route('transactions.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Thành viên</label>
                    <select name="user_id" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium focus:ring-2 focus:ring-emerald-500">
                        @foreach($members as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phân loại</label>
                    <select name="type" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium focus:ring-2 focus:ring-emerald-500">
                        <option value="contribution">🟢 Góp Quỹ (Thu vào)</option>
                        <option value="expense">🔴 Chi Tiêu Nhóm (Chi ra)</option>
                        <option value="loan">🟣 Vay Cá Nhân</option>
                        <option value="repayment">🔵 Trả Nợ Vay</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Số tiền (VNĐ)</label>
                    <input type="number" name="amount" required step="1000" min="1000" placeholder="VD: 500000" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-bold focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nội dung / Lí do</label>
                    <input type="text" name="description" required placeholder="VD: Tiền mua nước uống họp team..." class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2.5 text-xs font-bold text-slate-500">Hủy</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-xs font-bold shadow-md hover:bg-emerald-700 active:scale-95 transition">Lưu Giao Dịch</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: CHỈNH SỬA GIAO DỊCH -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="showEditModal = false" class="bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl p-5 sm:p-6 w-full sm:max-w-md shadow-2xl border border-slate-100 dark:border-slate-700 max-h-[90vh] overflow-y-auto">
            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-4">✏️ Chỉnh Sửa Giao Dịch</h3>

            <template x-if="selectedTx">
                <form :action="`/transactions/${selectedTx.id}`" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Thành viên</label>
                        <select name="user_id" x-model="selectedTx.user_id" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium">
                            @foreach($members as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Loại Giao Dịch</label>
                        <select name="type" x-model="selectedTx.type" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium">
                            <option value="contribution">Góp quỹ (Thu)</option>
                            <option value="expense">Chi tiêu chung (Chi)</option>
                            <option value="loan">Vay cá nhân</option>
                            <option value="repayment">Trả nợ vay</option>
                            <option value="distribution">Chia tiền %</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Số tiền (VNĐ)</label>
                        <input type="number" name="amount" x-model="selectedTx.amount" required step="1000" min="1000" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nội dung</label>
                        <input type="text" name="description" x-model="selectedTx.description" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm">
                    </div>

                    <div class="p-3 bg-amber-50 dark:bg-amber-900/30 rounded-xl text-[11px] text-amber-800 dark:text-amber-200">
                        ⚠️ Sửa giao dịch đã duyệt sẽ tự động điều chỉnh lại Số dư quỹ và Dư nợ cá nhân.
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-2">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2.5 text-xs font-bold text-slate-500">Hủy</button>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 active:scale-95 transition">Cập Nhật</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- MODAL: XÓA GIAO DỊCH -->
    <div x-show="showDeleteModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="showDeleteModal = false" class="bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl p-5 sm:p-6 w-full sm:max-w-sm shadow-2xl border border-slate-100 dark:border-slate-700">
            <h3 class="text-base sm:text-lg font-bold text-rose-600 mb-2">🗑️ Xác Nhận Xóa</h3>
            
            <template x-if="selectedTx">
                <div class="space-y-3">
                    <p class="text-xs text-slate-600 dark:text-slate-300">
                        Bạn có chắc chắn muốn xóa giao dịch: <strong x-text="selectedTx.description"></strong> (<span x-text="new Intl.NumberFormat('vi-VN').format(selectedTx.amount) + 'đ'"></span>)?
                    </p>
                    <p class="text-[11px] text-slate-400">
                        Hệ thống sẽ hoàn tác tác động đối với số dư quỹ.
                    </p>

                    <form :action="`/transactions/${selectedTx.id}`" method="POST" class="flex items-center justify-end space-x-2 pt-3">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="showDeleteModal = false" class="px-4 py-2.5 text-xs font-bold text-slate-500">Hủy</button>
                        <button type="submit" class="px-5 py-2.5 bg-rose-600 text-white rounded-xl text-xs font-bold hover:bg-rose-700 active:scale-95 transition">Xóa</button>
                    </form>
                </div>
            </template>
        </div>
    </div>

    <!-- MODAL: PHÂN CHIA LỢI NHUẬN % -->
    <div x-show="showDistributionModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="showDistributionModal = false" class="bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl p-5 sm:p-6 w-full sm:max-w-lg shadow-2xl border border-slate-100 dark:border-slate-700 max-h-[90vh] overflow-y-auto">
            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">📊 Chia Quỹ Theo % Cổ Phần</h3>
            <p class="text-[11px] sm:text-xs text-slate-500 mb-4">Nhập tổng tiền cần chia, hệ thống phân bổ theo tỷ lệ %.</p>

            <form action="{{ route('distributions.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Số tiền đem chia (VNĐ)</label>
                    <input type="number" name="total_amount" x-model="distributeAmount" required step="1000" min="1000" max="{{ $fund->balance }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-base font-extrabold text-emerald-600 focus:ring-2 focus:ring-emerald-500">
                    <p class="text-[10px] text-slate-400 mt-1">Tối đa: {{ number_format($fund->balance, 0, ',', '.') }}đ</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ghi chú</label>
                    <input type="text" name="note" placeholder="VD: Chia lợi nhuận quý 3..." class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Live Preview -->
                <div class="bg-slate-50 dark:bg-slate-700/40 p-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-600/60 space-y-2">
                    <h4 class="text-[10px] sm:text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Bảng phân bổ:</h4>
                    <template x-for="payout in calculatedPayouts" :key="payout.id">
                        <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-200/40 dark:border-slate-600/40 last:border-0">
                            <div class="flex items-center space-x-2">
                                <span class="w-6 h-6 rounded-full bg-slate-800 text-white font-bold text-[10px] flex items-center justify-center flex-shrink-0" x-text="payout.avatar"></span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs" x-text="payout.name"></span>
                                <span class="text-[10px] text-slate-400">(<span x-text="payout.share"></span>%)</span>
                            </div>
                            <span class="font-extrabold text-emerald-600 dark:text-emerald-400 text-xs" x-text="new Intl.NumberFormat('vi-VN').format(payout.amount) + 'đ'"></span>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" @click="showDistributionModal = false" class="px-4 py-2.5 text-xs font-bold text-slate-500">Hủy</button>
                    <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-xl text-xs font-extrabold shadow-md active:scale-95 transition">Chia Tiền</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: QUẢN LÝ THÀNH VIÊN -->
    <div x-show="showMemberModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="showMemberModal = false" class="bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl p-5 sm:p-6 w-full sm:max-w-lg shadow-2xl border border-slate-100 dark:border-slate-700 max-h-[85vh] overflow-y-auto">
            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-3">👥 Thành Viên & % Cổ Phần</h3>

            <div class="space-y-2.5 mb-5">
                @foreach($members as $m)
                    <form action="{{ route('members.updateShare', $m) }}" method="POST" class="p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-full bg-slate-800 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">
                                    {{ $m->avatar ?? substr($m->name, 0, 2) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $m->name }}</p>
                                    <p class="text-[10px] text-slate-400 truncate">{{ $m->email }}</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-1.5 flex-shrink-0 ml-2">
                                <input type="number" name="share_percentage" value="{{ $m->share_percentage }}" step="0.1" min="0" max="100" class="w-16 px-2 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-xs font-bold text-center">
                                <span class="text-xs font-bold text-slate-500">%</span>
                                <button type="submit" class="px-2 py-1.5 bg-emerald-600 text-white rounded-lg text-[10px] font-bold hover:bg-emerald-700">Lưu</button>
                            </div>
                        </div>
                    </form>
                @endforeach
            </div>

            <!-- Form Add Member -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-700">
                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">+ Thêm Thành Viên Mới</h4>
                <form action="{{ route('members.store') }}" method="POST" class="space-y-2">
                    @csrf
                    <input type="text" name="name" required placeholder="Họ và tên" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs">
                    <input type="email" name="email" required placeholder="Email" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs">
                    <div class="flex items-center space-x-2">
                        <input type="number" name="share_percentage" required value="0" step="0.1" placeholder="% cổ phần" class="flex-1 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold">
                        <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold flex-shrink-0">Thêm</button>
                    </div>
                </form>
            </div>

            <div class="flex items-center justify-end pt-4">
                <button type="button" @click="showMemberModal = false" class="px-4 py-2.5 text-xs font-bold text-slate-500">Đóng</button>
            </div>
        </div>
    </div>

</div>
@endsection
