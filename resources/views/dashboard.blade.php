@extends('layouts.app')

@section('content')
<div x-data="{ 
    mobileTab: 'entry',
    quickType: 'expense',
    quickDate: '{{ date('Y-m-d') }}',
    quickUserId: '{{ $members->first()->id ?? 1 }}',
    quickAmount: '',
    quickNote: 'Ăn uống',
    selectedCategory: 'eat',
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    showMemberModal: false,
    selectedTx: null,
    rawTransactions: {{ \Illuminate\Support\Js::from($allTransactions) }},

    // History Filters & Pagination State
    txSearchText: '',
    txFilterType: 'all',
    txFilterUserId: 'all',
    txFilterDate: '',
    txPage: 1,
    txPerPage: 10,

    get filteredTransactions() {
        return this.rawTransactions.filter(tx => {
            if (this.txFilterType !== 'all' && tx.type !== this.txFilterType) return false;
            if (this.txFilterUserId !== 'all' && String(tx.user_id) !== String(this.txFilterUserId)) return false;
            if (this.txFilterDate && (!tx.created_at || !tx.created_at.startsWith(this.txFilterDate))) return false;
            if (this.txSearchText) {
                let q = this.txSearchText.toLowerCase();
                let descMatch = tx.description && tx.description.toLowerCase().includes(q);
                let userMatch = tx.user_name && tx.user_name.toLowerCase().includes(q);
                let amountMatch = String(tx.amount).includes(q);
                if (!descMatch && !userMatch && !amountMatch) return false;
            }
            return true;
        });
    },

    get paginatedTransactions() {
        let start = (this.txPage - 1) * this.txPerPage;
        return this.filteredTransactions.slice(start, start + this.txPerPage);
    },

    get totalTxPages() {
        return Math.ceil(this.filteredTransactions.length / this.txPerPage) || 1;
    },

    expenseCategories: [
        { key: 'eat', name: 'Ăn uống', fullName: 'Ăn uống', icon: '/icons/EatAndDrink.svg', color: 'bg-amber-500' },
        { key: 'daily', name: 'Chi hàng ngày', fullName: 'Chi tiêu hàng ngày', icon: '/icons/DailyExpenses.svg', color: 'bg-emerald-500' },
        { key: 'clothes', name: 'Quần áo', fullName: 'Quần áo', icon: '/icons/Clothes.svg', color: 'bg-blue-500' },
        { key: 'cosmetics', name: 'Mỹ phẩm', fullName: 'Mỹ phẩm', icon: '/icons/Cosmetics.svg', color: 'bg-pink-500' },
        { key: 'exchange', name: 'Phí giao lưu', fullName: 'Phí giao lưu', icon: '/icons/Exchange.svg', color: 'bg-purple-500' },
        { key: 'medical', name: 'Y tế', fullName: 'Y tế', icon: '/icons/Medical.svg', color: 'bg-teal-500' },
        { key: 'education', name: 'Giáo dục', fullName: 'Giáo dục', icon: '/icons/Education.svg', color: 'bg-indigo-500' },
        { key: 'electric', name: 'Tiền điện', fullName: 'Tiền điện', icon: '/icons/Electric.svg', color: 'bg-yellow-500' },
        { key: 'transport', name: 'Đi lại', fullName: 'Đi lại', icon: '/icons/Transport.svg', color: 'bg-orange-500' },
        { key: 'contact', name: 'Phí liên lạc', fullName: 'Phí liên lạc', icon: '/icons/Contact.svg', color: 'bg-cyan-500' },
        { key: 'house', name: 'Tiền nhà', fullName: 'Tiền nhà', icon: '/icons/HouseRent.svg', color: 'bg-rose-500' }
    ],
    incomeCategories: [
        { key: 'salary', name: 'Tiền lương', fullName: 'Tiền lương', icon: '/icons/Salary.svg', color: 'bg-emerald-500' },
        { key: 'bonus', name: 'Tiền thưởng', fullName: 'Tiền thưởng', icon: '/icons/Bonus.svg', color: 'bg-amber-500' },
        { key: 'invest', name: 'Đầu tư', fullName: 'Lợi nhuận đầu tư', icon: '/icons/Invest.svg', color: 'bg-blue-500' },
        { key: 'other_income', name: 'Thu khác', fullName: 'Góp quỹ / Thu khác', icon: '/icons/Exchange.svg', color: 'bg-purple-500' }
    ],
    get activeCategories() {
        return this.quickType === 'expense' ? this.expenseCategories : this.incomeCategories;
    },
    switchQuickType(type) {
        this.quickType = type;
        if (type === 'expense') {
            this.selectCategory(this.expenseCategories[0]);
        } else {
            this.selectCategory(this.incomeCategories[0]);
        }
    },
    get formattedQuickDate() {
        let parts = this.quickDate.split('-');
        if (parts.length !== 3) return this.quickDate;
        let d = new Date(parts[0], parts[1] - 1, parts[2]);
        let day = String(d.getDate()).padStart(2, '0');
        let month = String(d.getMonth() + 1).padStart(2, '0');
        let year = d.getFullYear();
        let dayNames = ['CN', 'Th 2', 'Th 3', 'Th 4', 'Th 5', 'Th 6', 'Th 7'];
        return `${day}/${month}/${year} (${dayNames[d.getDay()]})`;
    },
    prevQuickDay() {
        let parts = this.quickDate.split('-');
        let d = new Date(parts[0], parts[1] - 1, parts[2]);
        d.setDate(d.getDate() - 1);
        let year = d.getFullYear();
        let month = String(d.getMonth() + 1).padStart(2, '0');
        let day = String(d.getDate()).padStart(2, '0');
        this.quickDate = `${year}-${month}-${day}`;
    },
    nextQuickDay() {
        let parts = this.quickDate.split('-');
        let d = new Date(parts[0], parts[1] - 1, parts[2]);
        d.setDate(d.getDate() + 1);
        let year = d.getFullYear();
        let month = String(d.getMonth() + 1).padStart(2, '0');
        let day = String(d.getDate()).padStart(2, '0');
        this.quickDate = `${year}-${month}-${day}`;
    },
    selectCategory(cat) {
        this.selectedCategory = cat.key;
        if (cat.fullName) {
            this.quickNote = cat.fullName;
        }
    }
}"
class="pb-20 lg:pb-6">

    <!-- Mobile View Switcher Tabs (hidden on desktop) -->
    <!-- Main Container: Desktop Multi-Column Grid -->
    <div class="grid grid-cols-1 gap-6">

        <!-- DASHBOARD OVERVIEW, CHARTS & MEMBER STATS -->
        <div class="space-y-6">

            <!-- 1. Top Stat Cards Row -->
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <!-- Card 1: Số Dư Quỹ -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm relative overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Số Dư Quỹ</span>
                    </div>
                    <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white mt-1.5">
                        {{ number_format($fund->balance, 0, ',', '.') }}<span class="text-base sm:text-lg font-bold">đ</span>
                    </p>
                </div>

                <!-- Card 3: Tổng Thu -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Tổng Thu</span>
                    </div>
                    <p class="text-lg sm:text-2xl font-extrabold text-blue-600 dark:text-blue-400 mt-1.5">
                        +{{ number_format($totalIncome, 0, ',', '.') }}<span class="text-sm sm:text-lg font-bold">đ</span>
                    </p>
                </div>

                <!-- Card 4: Tổng Chi -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Tổng Chi</span>
                    </div>
                    <p class="text-lg sm:text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-1.5">
                        -{{ number_format($totalExpense, 0, ',', '.') }}<span class="text-sm sm:text-lg font-bold">đ</span>
                    </p>
                </div>

                <!-- Card 5: Tổng Cho Vay -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Đang Cho Vay</span>
                    </div>
                    <p class="text-lg sm:text-2xl font-extrabold text-purple-600 dark:text-purple-400 mt-1.5">
                        {{ number_format($totalLoans, 0, ',', '.') }}<span class="text-sm sm:text-lg font-bold">đ</span>
                    </p>
                </div>
            </div>

            <!-- ApexCharts Section -->
            <div class="grid grid-cols-1 gap-4 sm:gap-5">
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
                    <div class="flex items-center space-x-3 mb-3 pb-2.5 border-b border-slate-100 dark:border-slate-700">
                        <div class="p-2 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-sm sm:text-base">Tỷ Lệ Dòng Tiền</h3>
                            <p class="text-[10px] text-slate-400 font-medium">Tổng Quan Thu / Chi / Cho Vay</p>
                        </div>
                    </div>
                    <!-- Custom SVG Donut Chart with Leader/Connector Lines -->
                    @php
                        $donutItems = [];
                        $donutTotal = max(1, $totalIncome + $totalExpense + $totalLoans);
                        $rawDonut = [
                            ['label' => 'Tổng Thu', 'value' => $totalIncome, 'color' => '#10b981'],
                            ['label' => 'Tổng Chi', 'value' => $totalExpense, 'color' => '#f43f5e'],
                            ['label' => 'Đang Vay', 'value' => $totalLoans, 'color' => '#f59e0b'],
                        ];
                        $cx = 220; $cy = 150; $outerR = 92; $innerR = 60;
                        $cumAngle = -90;
                        foreach ($rawDonut as $item) {
                            if ($item['value'] <= 0) continue;
                            $pct = ($item['value'] / $donutTotal) * 100;
                            $angle = ($pct / 100) * 360;
                            $startAngle = $cumAngle;
                            $endAngle = $cumAngle + $angle;
                            $cumAngle = $endAngle;
                            $effAngle = min($angle, 359.99);
                            $effEnd = $startAngle + $effAngle;
                            $sRad = deg2rad($startAngle); $eRad = deg2rad($effEnd);
                            $x1 = $cx + $outerR * cos($sRad); $y1 = $cy + $outerR * sin($sRad);
                            $x2 = $cx + $outerR * cos($eRad); $y2 = $cy + $outerR * sin($eRad);
                            $ix1 = $cx + $innerR * cos($eRad); $iy1 = $cy + $innerR * sin($eRad);
                            $ix2 = $cx + $innerR * cos($sRad); $iy2 = $cy + $innerR * sin($sRad);
                            $la = $effAngle > 180 ? 1 : 0;
                            $d = "M {$x1} {$y1} A {$outerR} {$outerR} 0 {$la} 1 {$x2} {$y2} L {$ix1} {$iy1} A {$innerR} {$innerR} 0 {$la} 0 {$ix2} {$iy2} Z";
                            $midRad = deg2rad(($startAngle + $endAngle) / 2);
                            $lsx = round($cx + $outerR * cos($midRad), 1);
                            $lsy = round($cy + $outerR * sin($midRad), 1);
                            $lex = round($cx + ($outerR + 24) * cos($midRad), 1);
                            $ley = round($cy + ($outerR + 24) * sin($midRad), 1);
                            $isR = $lex >= $cx;
                            $lhx = $isR ? $lex + 28 : $lex - 28;
                            $donutItems[] = [
                                'd' => $d, 'color' => $item['color'], 'label' => $item['label'],
                                'pct' => number_format($pct, 1, ',', '.'),
                                'lsx' => $lsx, 'lsy' => $lsy, 'lex' => $lex, 'ley' => $ley,
                                'lhx' => round($lhx, 1), 'lhy' => round($ley, 1),
                                'anchor' => $isR ? 'start' : 'end', 'txOff' => $isR ? 5 : -5,
                            ];
                        }
                    @endphp
                    <svg viewBox="0 0 440 300" class="w-full max-w-[480px] mx-auto" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                        {{-- Donut Slices --}}
                        @foreach($donutItems as $sl)
                            <path d="{{ $sl['d'] }}" fill="{{ $sl['color'] }}" class="transition-opacity duration-200 hover:opacity-80" />
                        @endforeach

                        {{-- Inner Hole --}}
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $innerR }}" class="fill-white dark:fill-slate-800" />

                        {{-- Center Label --}}
                        <text x="{{ $cx }}" y="{{ $cy - 10 }}" text-anchor="middle" font-size="9" font-weight="700" class="fill-slate-400 uppercase">Tổng Thu</text>
                        <text x="{{ $cx }}" y="{{ $cy + 10 }}" text-anchor="middle" font-size="15" font-weight="800" class="fill-slate-900 dark:fill-white">{{ number_format($fund->balance, 0, ',', '.') }}đ</text>

                        {{-- Connector Lines + Labels --}}
                        @foreach($donutItems as $sl)
                            <polyline points="{{ $sl['lsx'] }},{{ $sl['lsy'] }} {{ $sl['lex'] }},{{ $sl['ley'] }} {{ $sl['lhx'] }},{{ $sl['lhy'] }}" fill="none" stroke="{{ $sl['color'] }}" stroke-width="2" />
                            <circle cx="{{ $sl['lhx'] }}" cy="{{ $sl['lhy'] }}" r="3" fill="{{ $sl['color'] }}" />
                            <text x="{{ $sl['lhx'] + $sl['txOff'] }}" y="{{ $sl['lhy'] - 3 }}" text-anchor="{{ $sl['anchor'] }}" font-size="13" font-weight="800" class="fill-slate-900 dark:fill-white">{{ $sl['pct'] }}%</text>
                            <text x="{{ $sl['lhx'] + $sl['txOff'] }}" y="{{ $sl['lhy'] + 12 }}" text-anchor="{{ $sl['anchor'] }}" font-size="11" font-weight="700" fill="{{ $sl['color'] }}">{{ $sl['label'] }}</text>
                        @endforeach
                    </svg>

                    {{-- Legend --}}
                    <div class="flex items-center justify-center flex-wrap gap-x-5 gap-y-1.5 mt-3 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                        @foreach($rawDonut as $rd)
                            @if($rd['value'] > 0)
                            <div class="flex items-center space-x-1.5">
                                <span class="w-3 h-3 rounded-full" style="background: {{ $rd['color'] }}"></span>
                                <span class="text-xs font-extrabold text-slate-700 dark:text-slate-300">{{ $rd['label'] }}</span>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>


            <!-- 2. Full Transaction History Section (Lịch Sử GD Đầy Đủ) -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm space-y-4">
                


                <!-- Table View -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-400 font-extrabold text-[10px] uppercase tracking-wider">
                            <tr>
                                <th class="py-2.5 px-3 rounded-l-xl whitespace-nowrap">ID & Ngày</th>
                                <th class="py-2.5 px-3 whitespace-nowrap">Thành viên</th>
                                <th class="py-2.5 px-3">Nội dung ghi chú</th>
                                <th class="py-2.5 px-3 text-right rounded-r-xl whitespace-nowrap">Số tiền (VNĐ)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 font-semibold">
                            <template x-for="tx in paginatedTransactions" :key="tx.id">
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                                    <!-- ID & Date -->
                                    <td class="py-3 px-3 whitespace-nowrap">
                                        <p class="font-extrabold text-slate-900 dark:text-white font-mono text-[11px]" x-text="'#' + tx.id"></p>
                                        <p class="text-[10px] text-slate-400 font-medium" x-text="tx.created_at_formatted"></p>
                                    </td>

                                    <!-- Member -->
                                    <td class="py-3 px-3 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-6 h-6 rounded-full bg-slate-800 text-white font-bold text-[10px] flex items-center justify-center flex-shrink-0 overflow-hidden">
                                                <template x-if="tx.user_avatar && (tx.user_avatar.startsWith('http') || tx.user_avatar.startsWith('/uploads/'))">
                                                    <img :src="tx.user_avatar" :alt="tx.user_name" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!tx.user_avatar || (!tx.user_avatar.startsWith('http') && !tx.user_avatar.startsWith('/uploads/'))">
                                                    <span x-text="tx.user_avatar || (tx.user_name ? tx.user_name.substr(0, 2) : 'HV')"></span>
                                                </template>
                                            </div>
                                            <span class="font-bold text-slate-900 dark:text-white" x-text="tx.user_name"></span>
                                        </div>
                                    </td>

                                    <!-- Description -->
                                    <td class="py-3 px-3 min-w-[200px]">
                                        <p class="font-medium text-slate-800 dark:text-slate-200 line-clamp-1" x-text="tx.description"></p>
                                    </td>

                                    <!-- Amount -->
                                    <td class="py-3 px-3 text-right whitespace-nowrap">
                                        <template x-if="tx.type === 'contribution' || tx.type === 'repayment' || tx.type === 'adjustment'">
                                            <span class="font-black text-emerald-600 dark:text-emerald-400 text-xs" x-text="'+' + new Intl.NumberFormat('vi-VN').format(tx.amount) + 'đ'"></span>
                                        </template>
                                        <template x-if="tx.type !== 'contribution' && tx.type !== 'repayment' && tx.type !== 'adjustment'">
                                            <span class="font-black text-rose-600 dark:text-rose-400 text-xs" x-text="'-' + new Intl.NumberFormat('vi-VN').format(tx.amount) + 'đ'"></span>
                                        </template>
                                    </td>
                                </tr>
                            </template>

                            <template x-if="paginatedTransactions.length === 0">
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-400 font-semibold">
                                        Không tìm thấy giao dịch nào phù hợp với bộ lọc.
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Bar -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-700/60 text-xs font-bold text-slate-500">
                    <span x-text="'Trang ' + txPage + ' / ' + totalTxPages"></span>
                    <div class="flex items-center space-x-1.5">
                        <button type="button" @click="if (txPage > 1) txPage--" :disabled="txPage <= 1" class="px-3 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 rounded-lg transition disabled:opacity-40 cursor-pointer">
                            ◀ Trước
                        </button>
                        <button type="button" @click="if (txPage < totalTxPages) txPage++" :disabled="txPage >= totalTxPages" class="px-3 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 rounded-lg transition disabled:opacity-40 cursor-pointer">
                            Sau ▶
                        </button>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- MODAL: QUẢN LÝ THÀNH VIÊN -->
    <div x-show="showMemberModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="showMemberModal = false" class="bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl p-5 sm:p-6 w-full sm:max-w-lg shadow-2xl border border-slate-100 dark:border-slate-700 max-h-[85vh] overflow-y-auto">
            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-3">👥 Quản Lý Thành Viên</h3>

            <div class="space-y-2.5 mb-5">
                @foreach($members as $m)
                    <div class="p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl flex items-center justify-between">
                        <div class="flex items-center space-x-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-slate-800 text-white font-bold text-xs flex items-center justify-center flex-shrink-0 overflow-hidden">
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
                    </div>
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
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Tên đăng nhập (Username)</label>
                        <input type="text" name="username" required placeholder="vd: nhv, nda..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Địa Chỉ Email</label>
                        <input type="email" name="email" required placeholder="example@weamis.com" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Mật khẩu ban đầu</label>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold shadow-md transition cursor-pointer">Thêm Thành Viên</button>
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
