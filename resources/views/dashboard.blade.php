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
    filterSearch: '',
    filterMemberId: '',
    filterType: '',
    filterDateFrom: '',
    filterDateTo: '',
    sortOrder: 'desc',
    currentPage: 1,
    perPage: 10,
    rawTransactions: {{ \Illuminate\Support\Js::from($allTransactions) }},
    get filteredTransactions() {
        let search = this.filterSearch.toLowerCase().trim();
        let memberId = this.filterMemberId;
        let type = this.filterType;
        let dateFrom = this.filterDateFrom;
        let dateTo = this.filterDateTo;

        let filtered = this.rawTransactions.filter(tx => {
            let matchSearch = !search || (tx.description && tx.description.toLowerCase().includes(search));
            let matchMember = !memberId || tx.user_id == memberId;
            let matchType = !type || tx.type == type;

            let txDate = tx.created_at ? tx.created_at.substring(0, 10) : '';
            let matchFrom = !dateFrom || (txDate && txDate >= dateFrom);
            let matchTo = !dateTo || (txDate && txDate <= dateTo);

            return matchSearch && matchMember && matchType && matchFrom && matchTo;
        });

        let order = this.sortOrder;
        return filtered.sort((a, b) => {
            let dateA = a.created_at ? new Date(a.created_at).getTime() : 0;
            let dateB = b.created_at ? new Date(b.created_at).getTime() : 0;
            if (dateA === dateB) {
                return order === 'desc' ? b.id - a.id : a.id - b.id;
            }
            return order === 'desc' ? dateB - dateA : dateA - dateB;
        });
    },
    get totalPages() {
        return Math.max(1, Math.ceil(this.filteredTransactions.length / this.perPage));
    },
    get paginatedTransactions() {
        let start = (this.currentPage - 1) * this.perPage;
        return this.filteredTransactions.slice(start, start + this.perPage);
    },
    get pageNumbers() {
        let pages = [];
        let tp = this.totalPages;
        let cp = this.currentPage;
        if (tp <= 7) {
            for (let i = 1; i <= tp; i++) pages.push(i);
        } else {
            pages.push(1);
            if (cp > 3) pages.push('...');
            for (let i = Math.max(2, cp - 1); i <= Math.min(tp - 1, cp + 1); i++) pages.push(i);
            if (cp < tp - 2) pages.push('...');
            pages.push(tp);
        }
        return pages;
    },
    goToPage(n) { if (n >= 1 && n <= this.totalPages) this.currentPage = n; },
    prevPage() { if (this.currentPage > 1) this.currentPage--; },
    nextPage() { if (this.currentPage < this.totalPages) this.currentPage++; },
    resetFilters() {
        this.filterSearch = '';
        this.filterMemberId = '';
        this.filterType = '';
        this.filterDateFrom = '';
        this.filterDateTo = '';
        this.sortOrder = 'desc';
        this.currentPage = 1;
    },
    get calculatedPayouts() {
        let amount = parseFloat(this.distributeAmount) || 0;
        return [
            @foreach($members as $m)
            { id: {{ $m->id }}, name: '{{ $m->name }}', avatar: '{{ $m->avatar }}', share: {{ $m->share_percentage }}, amount: Math.round(amount * {{ $m->share_percentage }} / 100) },
            @endforeach
        ];
    }
}"
x-init="$watch('filterSearch', () => currentPage = 1); $watch('filterMemberId', () => currentPage = 1); $watch('filterType', () => currentPage = 1); $watch('filterDateFrom', () => currentPage = 1); $watch('filterDateTo', () => currentPage = 1); $watch('sortOrder', () => currentPage = 1)">

    <!-- Page Header & Action Bar -->
    <div class="flex flex-col gap-3 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                Báo Cáo Thu Chi & Quản Lý Quỹ
            </h2>
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
        <div class="col-span-2 sm:col-span-1 bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm relative overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
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
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
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
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
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
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
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

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5 mb-6"
         x-data="{
        isDark: document.documentElement.classList.contains('dark'),
        donutChart: null,
        barChart: null,
        updateTheme() {
            this.isDark = document.documentElement.classList.contains('dark');
            const textColor = this.isDark ? '#94a3b8' : '#64748b';
            const valueColor = this.isDark ? '#f8fafc' : '#0f172a';
            const foreColor = this.isDark ? '#f1f5f9' : '#1e293b';
            const bgColor = this.isDark ? '#1e293b' : '#ffffff';
            const gridColor = this.isDark ? '#334155' : '#e2e8f0';

            if (this.donutChart) {
                this.donutChart.updateOptions({
                    chart: { foreColor: foreColor },
                    stroke: { colors: [bgColor] },
                    legend: { labels: { colors: foreColor } },
                    tooltip: { theme: this.isDark ? 'dark' : 'light' },
                    plotOptions: {
                        pie: {
                            donut: {
                                labels: {
                                    name: { color: textColor },
                                    value: { color: valueColor },
                                    total: { color: textColor }
                                }
                            }
                        }
                    }
                });
            }

            if (this.barChart) {
                this.barChart.updateOptions({
                    chart: { foreColor: foreColor },
                    xaxis: { labels: { style: { colors: foreColor } } },
                    yaxis: { labels: { style: { colors: foreColor } } },
                    grid: { borderColor: gridColor },
                    tooltip: { theme: this.isDark ? 'dark' : 'light' }
                });
            }
        },
        initCharts() {
            this.isDark = document.documentElement.classList.contains('dark');
            const textColor = this.isDark ? '#94a3b8' : '#64748b';
            const valueColor = this.isDark ? '#f8fafc' : '#0f172a';
            const foreColor = this.isDark ? '#f1f5f9' : '#1e293b';
            const bgColor = this.isDark ? '#1e293b' : '#ffffff';
            const gridColor = this.isDark ? '#334155' : '#e2e8f0';

            // 1. Donut Chart: Thu / Chi / Vay
            this.donutChart = new ApexCharts(this.$refs.donutChart, {
                chart: { type: 'donut', height: 280, background: 'transparent', fontFamily: 'Plus Jakarta Sans, sans-serif', foreColor: foreColor },
                series: [{{ $totalIncome }}, {{ $totalExpense }}, {{ $totalLoans }}],
                labels: ['Tổng Thu (Góp + Trả nợ)', 'Tổng Chi', 'Đang Cho Vay'],
                colors: ['#10b981', '#f43f5e', '#f59e0b'],
                stroke: { width: 3, colors: [bgColor] },
                dataLabels: {
                    enabled: true,
                    style: { fontSize: '11px', fontWeight: 700, colors: ['#ffffff'] },
                    dropShadow: { enabled: true, top: 1, left: 1, blur: 2, opacity: 0.5 }
                },
                legend: {
                    position: 'bottom',
                    fontSize: '12px',
                    fontWeight: 600,
                    labels: { colors: foreColor },
                    markers: { width: 10, height: 10, radius: 3 }
                },
                tooltip: {
                    y: { formatter: (val) => new Intl.NumberFormat('vi-VN').format(val) + 'đ' },
                    theme: this.isDark ? 'dark' : 'light'
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '62%',
                            labels: {
                                show: true,
                                name: { fontSize: '12px', fontWeight: 700, color: textColor },
                                value: { fontSize: '18px', fontWeight: 800, color: valueColor, formatter: (val) => new Intl.NumberFormat('vi-VN').format(val) + 'đ' },
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Số Dư Quỹ',
                                    fontSize: '11px',
                                    fontWeight: 600,
                                    color: textColor,
                                    formatter: () => new Intl.NumberFormat('vi-VN').format({{ $fund->balance }}) + 'đ'
                                }
                            }
                        }
                    }
                }
            });
            this.donutChart.render();

            // 2. Bar Chart: Member Shares
            const memberNames = [@foreach($members as $m)'{{ $m->name }}',@endforeach];
            const memberShares = [@foreach($members as $m){{ $m->share_percentage }},@endforeach];

            this.barChart = new ApexCharts(this.$refs.barChart, {
                chart: { type: 'bar', height: 280, background: 'transparent', fontFamily: 'Plus Jakarta Sans, sans-serif', toolbar: { show: false }, foreColor: foreColor },
                series: [{ name: 'Cổ phần (%)', data: memberShares }],
                xaxis: {
                    categories: memberNames,
                    labels: {
                        style: { fontSize: '11px', fontWeight: 600, colors: foreColor },
                        formatter: (val) => val + '%'
                    }
                },
                yaxis: {
                    labels: {
                        style: { fontSize: '11px', fontWeight: 700, colors: foreColor }
                    },
                    max: Math.max(...memberShares) + 5
                },
                plotOptions: { bar: { horizontal: true, borderRadius: 6, barHeight: '60%', distributed: true } },
                colors: ['#6366f1', '#8b5cf6', '#a78bfa', '#c084fc', '#e879f9', '#f472b6', '#fb7185', '#f87171'],
                dataLabels: {
                    enabled: true,
                    formatter: (val) => val + '%',
                    style: { fontSize: '11px', fontWeight: 700, colors: ['#ffffff'] },
                    dropShadow: { enabled: true, top: 1, left: 1, blur: 2, opacity: 0.5 }
                },
                legend: { show: false },
                grid: { borderColor: gridColor, xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
                tooltip: {
                    y: { formatter: (val) => val + '% cổ phần' },
                    theme: this.isDark ? 'dark' : 'light'
                }
            });
            this.barChart.render();

            // Auto-update charts when theme toggles
            const observer = new MutationObserver(() => {
                this.updateTheme();
            });
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        }
     }"
         x-init="$nextTick(() => initCharts())"
    >
        <!-- Donut Chart Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
            <div class="flex items-center space-x-3 mb-4 pb-3 border-b border-slate-100 dark:border-slate-700">
                <div class="p-2 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-sm sm:text-base">Tỷ Lệ Dòng Tiền</h3>
                    <p class="text-[10px] text-slate-400 font-medium">Thu / Chi / Cho Vay</p>
                </div>
            </div>
            <div x-ref="donutChart"></div>
        </div>

        <!-- Bar Chart Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
            <div class="flex items-center space-x-3 mb-4 pb-3 border-b border-slate-100 dark:border-slate-700">
                <div class="p-2 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-sm sm:text-base">Cổ Phần Thành Viên</h3>
                    <p class="text-[10px] text-slate-400 font-medium">Tỷ lệ % góp vốn của từng thành viên</p>
                </div>
            </div>
            <div x-ref="barChart"></div>
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
            <div class="flex items-center space-x-3">
                <div class="p-2 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-sm sm:text-base">Thống Kê Cá Nhân</h3>
                    <p class="text-[10px] text-slate-400 font-medium">Chi tiết cổ phần & tài chính từng thành viên</p>
                </div>
            </div>
        </div>

        <!-- Desktop Table (hidden on mobile) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-700/60 dark:to-slate-700/30 text-slate-500 uppercase font-bold text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4 rounded-l-xl">Thành viên</th>
                        <th class="py-3 px-4">Cổ phần</th>
                        <th class="py-3 px-4">Đã Góp</th>
                        <th class="py-3 px-4">Đã Vay</th>
                        <th class="py-3 px-4">Đã Trả Nợ</th>
                        <th class="py-3 px-4">Đang Nợ</th>
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
                            <td class="py-3 px-4 font-bold text-slate-700 dark:text-slate-300">{{ number_format($stat['loans'], 0, ',', '.') }}đ</td>
                            <td class="py-3 px-4 font-bold text-teal-600 dark:text-teal-400">{{ number_format($stat['repaid'], 0, ',', '.') }}đ</td>
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

                    <div class="grid grid-cols-4 gap-1.5 text-center">
                        <div class="bg-white dark:bg-slate-800 p-1.5 rounded-lg">
                            <p class="text-[9px] text-slate-400 font-bold uppercase">Đã góp</p>
                            <p class="text-[11px] font-extrabold text-emerald-600">+{{ number_format($stat['contributed'], 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-white dark:bg-slate-800 p-1.5 rounded-lg">
                            <p class="text-[9px] text-slate-400 font-bold uppercase">Đã vay</p>
                            <p class="text-[11px] font-extrabold text-slate-700 dark:text-slate-300">{{ number_format($stat['loans'], 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-white dark:bg-slate-800 p-1.5 rounded-lg">
                            <p class="text-[9px] text-slate-400 font-bold uppercase">Đã trả</p>
                            <p class="text-[11px] font-extrabold text-teal-600">{{ number_format($stat['repaid'], 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-white dark:bg-slate-800 p-1.5 rounded-lg">
                            <p class="text-[9px] text-slate-400 font-bold uppercase">Đang nợ</p>
                            <p class="text-[11px] font-extrabold {{ $stat['debt'] > 0 ? 'text-rose-600' : 'text-slate-400' }}">
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
                <div class="flex items-center space-x-3">
                    <div class="p-2 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base sm:text-lg">Lịch Sử Giao Dịch</h3>
                        <p class="text-[10px] text-slate-400 font-medium">
                            <span x-text="filteredTransactions.length"></span> giao dịch
                            <template x-if="filterSearch || filterMemberId || filterType || filterDateFrom || filterDateTo || sortOrder !== 'desc'">
                                <span class="text-indigo-500 font-bold"> (đang lọc)</span>
                            </template>
                        </p>
                    </div>
                </div>

                <!-- Mobile filter toggle -->
                <button @click="showFilters = !showFilters" class="md:hidden px-3 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold flex items-center space-x-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    <span>Lọc</span>
                </button>
            </div>

            <!-- Reactive Filter Bar: Zero Reload, Instant Filter -->
            <div class="gap-2 grid-cols-1 sm:grid-cols-2 md:flex md:flex-wrap md:items-center" :class="showFilters ? 'grid' : 'hidden md:flex'">
                <input type="text" x-model="filterSearch" placeholder="🔍 Tìm kiếm nội dung..." class="w-full md:w-52 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-medium focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white">
                
                <select x-model="filterMemberId" class="w-full sm:w-auto px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white cursor-pointer">
                    <option value="">Tất cả thành viên</option>
                    @foreach($members as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                    @endforeach
                </select>

                <select x-model="filterType" class="w-full sm:w-auto px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white cursor-pointer">
                    <option value="">Tất cả loại GD</option>
                    <option value="contribution">Góp quỹ (Thu)</option>
                    <option value="expense">Chi tiêu (Chi)</option>
                    <option value="loan">Vay cá nhân</option>
                    <option value="repayment">Trả nợ vay</option>
                    <option value="distribution">Chia tiền %</option>
                </select>

                <!-- Lọc Theo Ngày Tháng Năm -->
                <div class="flex items-center space-x-1.5 w-full sm:w-auto bg-slate-50 dark:bg-slate-700/50 p-1 rounded-xl border border-slate-200 dark:border-slate-600">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 pl-1.5">📅</span>
                    <input type="date" x-model="filterDateFrom" title="Từ ngày" class="px-2 py-1 rounded-lg border-0 bg-white dark:bg-slate-800 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white cursor-pointer">
                    <span class="text-slate-400 text-xs font-bold">➔</span>
                    <input type="date" x-model="filterDateTo" title="Đến ngày" class="px-2 py-1 rounded-lg border-0 bg-white dark:bg-slate-800 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white cursor-pointer">
                </div>

                <!-- Sắp Xếp Ngày Giao Dịch -->
                <select x-model="sortOrder" class="w-full sm:w-auto px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white cursor-pointer">
                    <option value="desc">⬇️ Mới nhất ➔ Cũ nhất</option>
                    <option value="asc">⬆️ Cũ nhất ➔ Mới nhất</option>
                </select>

                <template x-if="filterSearch || filterMemberId || filterType || filterDateFrom || filterDateTo || sortOrder !== 'desc'">
                    <button @click="resetFilters()" class="px-3 py-2 text-xs font-bold text-rose-600 hover:text-rose-700 hover:underline flex items-center space-x-1 cursor-pointer">
                        <span>✕ Xóa lọc</span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Desktop Data Table (hidden on mobile) -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-700/60 dark:to-slate-700/30 text-slate-500 uppercase font-bold text-[10px] tracking-wider">
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
                    <template x-for="(tx, index) in paginatedTransactions" :key="tx.id">
                        <tr class="hover:bg-indigo-50/40 dark:hover:bg-indigo-900/10 transition-colors duration-150">
                            <td class="py-3.5 px-4 font-bold text-slate-400" x-text="sortOrder === 'asc' ? ((currentPage - 1) * perPage + index + 1) : (filteredTransactions.length - ((currentPage - 1) * perPage + index))"></td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-slate-500" x-text="tx.created_at_formatted"></td>
                            <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                <div class="flex items-center space-x-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-[10px] flex items-center justify-center flex-shrink-0 overflow-hidden">
                                        <template x-if="tx.user_avatar && (tx.user_avatar.startsWith('http') || tx.user_avatar.startsWith('/uploads/'))">
                                            <img :src="tx.user_avatar" :alt="tx.user_name" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!tx.user_avatar || (!tx.user_avatar.startsWith('http') && !tx.user_avatar.startsWith('/uploads/'))">
                                            <span x-text="tx.user_avatar || (tx.user_name ? tx.user_name.substr(0, 2) : 'HV')"></span>
                                        </template>
                                    </div>
                                    <span x-text="tx.user_name"></span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <template x-if="tx.type === 'contribution'">
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 font-bold rounded">Góp quỹ</span>
                                </template>
                                <template x-if="tx.type === 'expense'">
                                    <span class="px-2 py-0.5 bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 font-bold rounded">Chi tiêu</span>
                                </template>
                                <template x-if="tx.type === 'loan'">
                                    <span class="px-2 py-0.5 bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 font-bold rounded">Vay</span>
                                </template>
                                <template x-if="tx.type === 'repayment'">
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 font-bold rounded">Trả nợ</span>
                                </template>
                                <template x-if="tx.type === 'distribution'">
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 font-bold rounded">Chia %</span>
                                </template>
                            </td>
                            <td class="py-3.5 px-4 font-extrabold whitespace-nowrap">
                                <template x-if="tx.type === 'contribution' || tx.type === 'repayment'">
                                    <span class="text-emerald-600 dark:text-emerald-400" x-text="'+' + new Intl.NumberFormat('vi-VN').format(tx.amount) + 'đ'"></span>
                                </template>
                                <template x-if="tx.type !== 'contribution' && tx.type !== 'repayment'">
                                    <span class="text-slate-900 dark:text-slate-100" x-text="'-' + new Intl.NumberFormat('vi-VN').format(tx.amount) + 'đ'"></span>
                                </template>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-800 dark:text-slate-200 max-w-sm break-words whitespace-normal leading-relaxed" :title="tx.description" x-text="tx.description"></td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <template x-if="tx.status === 'approved'">
                                    <span class="text-emerald-600 font-bold">✓ Duyệt</span>
                                </template>
                                <template x-if="tx.status === 'pending'">
                                    <span class="text-amber-600 font-bold">⏳ Chờ</span>
                                </template>
                                <template x-if="tx.status === 'rejected'">
                                    <span class="text-slate-400 font-bold line-through">Hủy</span>
                                </template>
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap space-x-1">
                                <button @click="selectedTx = tx; showEditModal = true" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded font-bold transition cursor-pointer">✏️ Sửa</button>
                                <button @click="selectedTx = tx; showDeleteModal = true" class="px-2.5 py-1 bg-rose-50 dark:bg-rose-900/30 hover:bg-rose-600 hover:text-white text-rose-600 font-bold rounded transition cursor-pointer">🗑️ Xóa</button>
                            </td>
                        </tr>
                    </template>

                    <template x-if="filteredTransactions.length === 0">
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-400">Không tìm thấy giao dịch nào phù hợp.</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Mobile Transaction Cards (hidden on desktop) -->
        <div class="lg:hidden space-y-2.5">
            <template x-for="(tx, index) in paginatedTransactions" :key="tx.id">
                <div class="p-3.5 bg-slate-50 dark:bg-slate-700/30 rounded-xl border border-slate-100 dark:border-slate-700 hover:shadow-md transition-shadow duration-150">
                    <!-- Row 1: User + Amount -->
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-slate-200 font-bold text-[10px] flex items-center justify-center flex-shrink-0 overflow-hidden">
                                <template x-if="tx.user_avatar && (tx.user_avatar.startsWith('http') || tx.user_avatar.startsWith('/uploads/'))">
                                    <img :src="tx.user_avatar" :alt="tx.user_name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!tx.user_avatar || (!tx.user_avatar.startsWith('http') && !tx.user_avatar.startsWith('/uploads/'))">
                                    <span x-text="tx.user_avatar || (tx.user_name ? tx.user_name.substr(0, 2) : 'HV')"></span>
                                </template>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="tx.user_name"></p>
                                <p class="text-[10px] text-slate-400" x-text="tx.created_at_formatted"></p>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0 ml-2">
                            <template x-if="tx.type === 'contribution' || tx.type === 'repayment'">
                                <p class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400" x-text="'+' + new Intl.NumberFormat('vi-VN').format(tx.amount) + 'đ'"></p>
                            </template>
                            <template x-if="tx.type !== 'contribution' && tx.type !== 'repayment'">
                                <p class="text-sm font-extrabold text-slate-900 dark:text-slate-100" x-text="'-' + new Intl.NumberFormat('vi-VN').format(tx.amount) + 'đ'"></p>
                            </template>
                        </div>
                    </div>

                    <!-- Row 2: Badge + Description -->
                    <div class="flex items-start space-x-2 mb-2.5">
                        <template x-if="tx.type === 'contribution'">
                            <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 font-bold rounded text-[10px] flex-shrink-0">Góp quỹ</span>
                        </template>
                        <template x-if="tx.type === 'expense'">
                            <span class="px-1.5 py-0.5 bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 font-bold rounded text-[10px] flex-shrink-0">Chi tiêu</span>
                        </template>
                        <template x-if="tx.type === 'loan'">
                            <span class="px-1.5 py-0.5 bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 font-bold rounded text-[10px] flex-shrink-0">Vay</span>
                        </template>
                        <template x-if="tx.type === 'repayment'">
                            <span class="px-1.5 py-0.5 bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 font-bold rounded text-[10px] flex-shrink-0">Trả nợ</span>
                        </template>
                        <template x-if="tx.type === 'distribution'">
                            <span class="px-1.5 py-0.5 bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 font-bold rounded text-[10px] flex-shrink-0">Chia %</span>
                        </template>
                        <p class="text-[11px] text-slate-600 dark:text-slate-300 font-medium leading-relaxed" x-text="tx.description"></p>
                    </div>

                    <!-- Row 3: Status + Actions -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 dark:border-slate-600/40">
                        <div>
                            <template x-if="tx.status === 'approved'">
                                <span class="text-[10px] text-emerald-600 font-bold">✓ Đã duyệt</span>
                            </template>
                            <template x-if="tx.status === 'pending'">
                                <span class="text-[10px] text-amber-600 font-bold">⏳ Chờ duyệt</span>
                            </template>
                            <template x-if="tx.status === 'rejected'">
                                <span class="text-[10px] text-slate-400 font-bold line-through">Từ chối</span>
                            </template>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <button @click="selectedTx = tx; showEditModal = true" class="px-3 py-1.5 bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-[10px] font-bold active:scale-95 transition cursor-pointer">✏️ Sửa</button>
                            <button @click="selectedTx = tx; showDeleteModal = true" class="px-3 py-1.5 bg-rose-100 dark:bg-rose-900/30 text-rose-600 rounded-lg text-[10px] font-bold active:scale-95 transition cursor-pointer">🗑️ Xóa</button>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="filteredTransactions.length === 0">
                <p class="text-center py-8 text-xs text-slate-400">Không tìm thấy giao dịch nào phù hợp.</p>
            </template>
        </div>

        <!-- Alpine.js Client-Side Pagination -->
        <template x-if="totalPages > 1">
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                    <!-- Info -->
                    <p class="text-[11px] text-slate-400 font-medium">
                        Hiển thị <span class="font-bold text-slate-600 dark:text-slate-300" x-text="((currentPage - 1) * perPage + 1)"></span>–<span class="font-bold text-slate-600 dark:text-slate-300" x-text="Math.min(currentPage * perPage, filteredTransactions.length)"></span> / <span class="font-bold text-slate-600 dark:text-slate-300" x-text="filteredTransactions.length"></span> giao dịch
                    </p>

                    <!-- Page Controls -->
                    <div class="flex items-center space-x-1">
                        <button @click="prevPage()" :disabled="currentPage <= 1" class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all duration-150 cursor-pointer" :class="currentPage <= 1 ? 'text-slate-300 dark:text-slate-600 cursor-not-allowed' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>

                        <template x-for="page in pageNumbers" :key="page">
                            <button
                                @click="typeof page === 'number' && goToPage(page)"
                                class="min-w-[32px] h-8 rounded-lg text-xs font-bold transition-all duration-150"
                                :class="{
                                    'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-md shadow-indigo-500/25': page === currentPage,
                                    'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer': page !== currentPage && typeof page === 'number',
                                    'text-slate-400 cursor-default': typeof page !== 'number'
                                }"
                                x-text="page"
                            ></button>
                        </template>

                        <button @click="nextPage()" :disabled="currentPage >= totalPages" class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all duration-150 cursor-pointer" :class="currentPage >= totalPages ? 'text-slate-300 dark:text-slate-600 cursor-not-allowed' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>
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
                            <div class="flex items-center space-x-2 min-w-0">
                                <div class="w-6 h-6 rounded-full bg-slate-800 text-white font-bold text-[10px] flex items-center justify-center flex-shrink-0 overflow-hidden">
                                    <template x-if="payout.avatar && (payout.avatar.startsWith('http') || payout.avatar.startsWith('/uploads/'))">
                                        <img :src="payout.avatar" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!payout.avatar || (!payout.avatar.startsWith('http') && !payout.avatar.startsWith('/uploads/'))">
                                        <span x-text="payout.avatar || payout.name.substr(0, 2)"></span>
                                    </template>
                                </div>
                                <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs truncate" x-text="payout.name"></span>
                                <span class="text-[10px] text-slate-400 flex-shrink-0">(<span x-text="payout.share"></span>%)</span>
                            </div>
                            <span class="font-extrabold text-emerald-600 dark:text-emerald-400 text-xs flex-shrink-0 ml-2" x-text="new Intl.NumberFormat('vi-VN').format(payout.amount) + 'đ'"></span>
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
                                <div class="w-7 h-7 rounded-full bg-slate-800 text-white font-bold text-xs flex items-center justify-center flex-shrink-0 overflow-hidden">
                                    @if($m->avatar && \Illuminate\Support\Str::startsWith($m->avatar, ['http://', 'https://', '/uploads/']))
                                        <img src="{{ $m->avatar }}" alt="{{ $m->name }}" class="w-full h-full object-cover">
                                    @else
                                        {{ $m->avatar ?? substr($m->name, 0, 2) }}
                                    @endif
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
                <h4 class="text-xs font-extrabold text-slate-900 dark:text-white mb-3">➕ Thêm Thành Viên Mới</h4>
                <form action="{{ route('members.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Họ và Tên</label>
                        <input type="text" name="name" required placeholder="Nhập họ và tên thành viên..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Địa Chỉ Email</label>
                        <input type="email" name="email" required placeholder="example@weamis.com" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">% Cổ Phần Ban Đầu</label>
                        <div class="flex items-center space-x-2">
                            <div class="relative flex-1">
                                <input type="number" name="share_percentage" required placeholder="Ví dụ: 12.5" step="0.1" min="0" max="100" class="w-full px-3.5 py-2 pr-7 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                <span class="absolute right-3 top-2 text-xs font-bold text-slate-400">%</span>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold flex-shrink-0 shadow-md transition cursor-pointer">Thêm</button>
                        </div>
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
